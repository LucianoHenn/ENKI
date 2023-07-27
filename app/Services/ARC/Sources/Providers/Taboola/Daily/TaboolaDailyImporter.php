<?php

namespace App\Services\ARC\Sources\Providers\Taboola\Daily;

use App\Services\ARC\Sources\Abstracts\BaseImporter;
use App\Models\CurrencyConversion;
use App\Models\ARC\ReportLogbook;
use App\Models\ARC\Providers\TaboolaDailyReportData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use Exception;
use Illuminate\Support\Carbon as Carbon;
use Illuminate\Support\Facades\Storage as Storage;

use App\Models\ClientArcAssociation;

/**
 * Class TaboolaDailyImporter
 */
class TaboolaDailyImporter extends BaseImporter
{

    public $send_created_event = false;

    public function doImport(ReportLogbook $request)
    {
        $identifier = $request->identifier;
        $table = $this->getReportTableName($request);

        Log::info("[TaboolaDailyImporter] Start importing for identifier {$identifier}");

        try {
            $count = 0;
            // Read the CSV
            $localReport = $request->infoOriginalLocalReport;

            // Set read file and set header
            $json = json_decode(Storage::disk('system')->get($localReport));

            // Check CSV size
            if (empty($json) || sizeof($json->results) == 0) {
                Log::info("[TaboolaDailyImporter] Empty data on {$localReport}");
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

            // TODO
            // Write info into db using model

            collect($json->results)
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
        $currency = strtoupper($row->currency);

        $amount = $row->spent ?? 0;
        $amount_eur = $amount_usd = 0;
        $date   = Carbon::parse($row->date)->format('Y-m-d');

        if ($currency === "EUR") {
            $amount_eur = $amount;
            $amount_usd = CurrencyConversion::convertAmount($date, $currency, 'USD', $amount, 4, true);
        } else { //$currency === "USD") {
            $amount_usd = $amount;
            $amount_eur = CurrencyConversion::convertAmount($date, $currency, 'EUR', $amount, 4, true);
        }
        $timestamp = Carbon::now();

        $record = [
            'date'                  => $date,
            'identifier'            => $request->identifier,

            'client_id'             => $activeAssoc->client_id,
            'market_id'             => $activeAssoc->market_id,
            'market'                => $activeAssoc->market->code,

            // FILL MODEL
            'campaign_name'                 => $row->campaign_name,
            'campaign_id'                   => $row->campaign,
            'clicks'                        => $row->clicks ?? 0,
            'impressions'                   => $row->impressions ?? 0,
            'visible_impressions'           => $row->visible_impressions ?? 0,
            'conversions_value'             => $row->conversions_value ?? 0,
            'roas'                          => $row->roas ?? 0,
            'ctr'                           => $row->ctr ?? 0,
            'vctr'                          => $row->vctr ?? 0,
            'cpm'                           => $row->cpm ?? 0,
            'vcpm'                          => $row->vcpm ?? 0,
            'cpc'                           => $row->cpc ?? 0,
            'campaigns_num'                 => $row->campaigns_num ?? 0,
            'cpa'                           => $row->cpa ?? 0,
            'cpa_clicks'                    => $row->cpa_clicks ?? 0,
            'cpa_views'                     => $row->cpa_views ?? 0,
            'cpa_actions_num'               => $row->cpa_actions_num ?? 0,
            'cpa_actions_num_from_clicks'   => $row->cpa_actions_num_from_clicks ?? 0,
            'cpa_actions_num_from_views'    => $row->cpa_actions_num_from_views ?? 0,
            'cpa_conversion_rate'           => $row->cpa_conversion_rate ?? 0,
            'cpa_conversion_rate_clicks'    => $row->cpa_conversion_rate_clicks ?? 0,
            'cpa_conversion_rate_views'     => $row->cpa_conversion_rate_views ?? 0,

            'currency'              => $currency,
            'revenue_share'         => 1,
            'amount'                => $amount,
            'amount_eur'            => $amount_eur,
            'amount_usd'            => $amount_usd,

            'created_at'            => $timestamp,
            'updated_at'            => $timestamp,
        ];

        return $record;
    }
}
