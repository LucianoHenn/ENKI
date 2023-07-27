<?php

namespace App\Services\ARC\Sources\Providers\Facebook\Campaigns;

use App\Services\ARC\Sources\Abstracts\BaseImporter;
use App\Models\CurrencyConversion;
use App\Models\ARC\ReportLogbook;
use App\Models\ARC\Providers\Facebook\FacebookCampaignsReportData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;



use App\Models\ClientArcAssociation;

use Exception;
use Illuminate\Support\Carbon as Carbon;
use Illuminate\Support\Facades\Storage as Storage;

/**
 * Class FacebookCampaignsImporter
 */
class FacebookCampaignsImporter extends BaseImporter
{

    public $send_created_event = false;

    public function doImport(ReportLogbook $request)
    {
        $identifier = $request->identifier;
        $table = $this->getReportTableName($request);

        Log::info("[FacebookCampaignsImporter] Start importing for identifier {$identifier}");

        try {
            $count = 0;
            // Read the CSV
            $localReport = $request->infoOriginalLocalReport;

            // Set read file and set header
            $json = json_decode(Storage::disk('system')->get($localReport));

            if (empty($json)) { // Note: depends from source
                Log::info("[FacebookCampaignsImporter] Empty data on {$localReport}");
                return false;
            }

            // Clear data BEFORE importing new data
            $this->deleteData($request);

            $validAssociations = ClientArcAssociation::where('source', $this->source)
                ->inPeriod($request->date_end)
                ->get();

            $activeAssoc = null;
            foreach ($validAssociations as $assoc) {
                if ($assoc->info['account_id'] == $request->identifier) {
                    $activeAssoc = $assoc;
                    break;
                }
            }

            collect($json)
                ->chunk($this->insert_chunk_size)
                ->each(function ($chunkDataRows) use ($request, $table, $identifier, $activeAssoc, &$count) {
                    $toInsert = [];
                    foreach ($chunkDataRows as $dataRow) {
                        $record = $this->processRecord($dataRow, $request, $activeAssoc);
                        $toInsert[] = $record;
                        $count++;
                    }
                    // Can we use also a factory method
                    $this->insert($table, $toInsert);
                });

            return $count;
        } catch (Exception $e) {
            // In case of exception, set update failed and rollBack DB
            throw $e;
        }
    }


    private function processRecord($row, ReportLogbook $request, ClientArcAssociation $activeAssoc)
    {

        $timestamp = Carbon::now();

        $account_currency = strtoupper($activeAssoc->info['account_currency']);


        $daily_budget = $row->daily_budget ?? 0;
        $daily_budget_eur = CurrencyConversion::convertAmount($request->date_end, $account_currency, 'EUR', $daily_budget, 4, true);
        $daily_budget_usd = CurrencyConversion::convertAmount($request->date_end, $account_currency, 'USD', $daily_budget, 4, true);

        $lifetime_budget = $row->lifetime_budget ?? 0;
        $lifetime_budget_eur = CurrencyConversion::convertAmount($request->date_end, $account_currency, 'EUR', $lifetime_budget, 4, true);
        $lifetime_budget_usd = CurrencyConversion::convertAmount($request->date_end, $account_currency, 'USD', $lifetime_budget, 4, true);


        $record = [
            'date'                  => $request->date_end,
            'identifier'            => $request->identifier,

            'client_id'             => $activeAssoc->client_id,
            'market_id'             => $activeAssoc->market_id,
            'market'                => $activeAssoc->market->code,

            'account_id'            => $row->account_id,
            'campaign_id'           => $row->id,
            'name'                  => $row->name,
            'objective'             => $row->objective,
            'status'                => $row->status,
            'effective_status'      => $row->effective_status,
            'special_ad_categories' => json_encode($row->special_ad_categories ?? []),

            'created_time'          => !empty($row->created_time) ? Carbon::parse($row->created_time) : null,
            'updated_time'          => !empty($row->updated_time) ? Carbon::parse($row->updated_time) : null,
            'start_time'            => !empty($row->start_time) ? Carbon::parse($row->start_time) : null,
            'end_time'              => !empty($row->end_time) ? Carbon::parse($row->end_time) : null,

            'daily_budget'          => $daily_budget,
            'lifetime_budget'       => $lifetime_budget,

            'account_currency'      => $account_currency,
            'daily_budget_eur'      => $daily_budget_eur,
            'daily_budget_usd'      => $daily_budget_usd,
            'lifetime_budget_eur'   => $lifetime_budget_eur,
            'lifetime_budget_usd'   => $lifetime_budget_usd,


            'created_at'            => $timestamp,
            'updated_at'            => $timestamp,
        ];

        return $record;
    }
}
