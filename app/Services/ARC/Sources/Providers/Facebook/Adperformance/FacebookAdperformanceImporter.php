<?php

namespace App\Services\ARC\Sources\Providers\Facebook\Adperformance;

use App\Services\ARC\Sources\Abstracts\BaseImporter;
use App\Models\CurrencyConversion;
use App\Models\ARC\ReportLogbook;
use App\Models\ARC\Providers\Facebook\FacebookAdperformanceReportData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Models\ClientArcAssociation;

use Exception;
use Illuminate\Support\Carbon as Carbon;
use Illuminate\Support\Facades\Storage as Storage;

/**
 * Class FacebookAdperformanceImporter
 */
class FacebookAdperformanceImporter extends BaseImporter
{

    public $send_created_event = false;
    public $revenue_share = 1.0;

    public function doImport(ReportLogbook $request)
    {
        $identifier = $request->identifier;
        $table = $this->getReportTableName($request);

        Log::info("[FacebookAdperformanceImporter] Start importing for identifier {$identifier}");

        try {
            $count = 0;
            // Read the CSV
            $localReport = $request->infoOriginalLocalReport;

            // Set read file and set header
            $json = json_decode(Storage::disk('system')->get($localReport));

            if (empty($json->data)) { // Note: depends from source
                Log::info("[FacebookAdperformanceImporter] Empty data on {$localReport}");
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

            collect($json->data)
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

        $account_currency = strtoupper($row->account_currency);


        $amount = $amount_usd = $amount_eur = $row->spend ?? 0;
        
        $amount_eur = CurrencyConversion::convertAmount($request->date_end, $account_currency, 'EUR', $amount, 4, true);
        $amount_usd = CurrencyConversion::convertAmount($request->date_end, $account_currency, 'USD', $amount, 4, true);

        $actions = [];

        if(!empty($row->actions)) {
            foreach($row->actions as $act) {
                $actions[$act->action_type] = $act->value ?? 0;
            }
        }

        $record = [
            'date'                  => $request->date_end,
            'identifier'            => $request->identifier,

            'client_id'             => $activeAssoc->client_id,
            'market_id'             => $activeAssoc->market_id,
            'market'                => $activeAssoc->market->code,

            'account_id'            => $row->account_id,
            'account_name'          => $row->account_name,
            'campaign_id'           => $row->campaign_id,
            'adset_id'              => $row->adset_id,
            'ad_id'                 => $row->ad_id,
            'ad_name'               => $row->ad_name,

            'date_start'            => $row->date_start ?? null,
            'date_end'              => $row->date_end ?? null,

            'buying_type'           => $row->buying_type ?? '',
            'optimization_goal'         => $row->optimization_goal ?? '',

            'frequency'                 => $row->frequency ?? 0,
            'clicks'                    => $row->clicks ?? 0,
            'inline_link_clicks'        => $row->inline_link_clicks ?? 0,
            'inline_post_engagement'    => $row->inline_post_engagement ?? 0,
            'impressions'               => $row->impressions ?? 0,
            'reach'                     => $row->reach ?? 0,

            'actions'                   => json_encode($actions),
            'impression_device'         => $row->impression_device ?? '',
            'publisher_platform'        => $row->publisher_platform ?? '',

            'hash'                      => FacebookAdperformanceReportData::getHash([
                                            $row->ad_id,
                                            $row->ad_name,
                                            ( $row->impression_device ?? '' ),
                                            ( $row->publisher_platform ?? '' ),
                                            ($row->buying_type ?? ''),
                                            ($row->optimization_goal ?? '')
                                        ]),
            'currency'              => $account_currency,
            'revenue_share'         => $this->revenue_share,
            'amount'                => $amount,
            'amount_eur'            => $amount_eur,
            'amount_usd'            => $amount_usd,
            'created_at'            => $timestamp,
            'updated_at'            => $timestamp,
        ];
        return $record;
    }
}
