<?php

namespace App\Services\ARC\Sources\Providers\TikTok\Daily;

use App\Services\ARC\Sources\Abstracts\BaseImporter;
use App\Models\CurrencyConversion;
use App\Models\ARC\ReportLogbook;
use App\Models\ARC\Providers\TikTok\TikTokDailyReportData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


use Exception;
use Illuminate\Support\Carbon as Carbon;
use Illuminate\Support\Facades\Storage as Storage;
use App\Models\ClientArcAssociation;

/**
 * Class TikTokDailyImporter
 */
class TikTokDailyImporter extends BaseImporter
{

    public $send_created_event = false;

    public function doImport(ReportLogbook $request)
    {
        $identifier = $request->identifier;
        $table = $this->getReportTableName($request);

        Log::info("[TikTokDailyImporter] Start importing for identifier {$identifier}");

        try {
            $localReport = $request->infoOriginalLocalReport;

            $response = json_decode(Storage::disk('system')->get($localReport));
            
            $data = $response->response->data->list ?? [];
            $totalItems = 0;
            if (!empty($data)) {
                $totalItems = count($data) ?? 0;
            }

            // Check Data size
            if ($totalItems < 1) { // Note: depends from source
                Log::info("[TikTokDailyImporter] Empty data on {$localReport}");
                return false;
            }

            // Clear data BEFORE importing new data
            $this->deleteData($request);

            $validAssociations = ClientArcAssociation::where('source', $this->source)
                ->inPeriod($request->date_end)
                ->get();

            $activeAssoc = null;
            foreach ($validAssociations as $assoc) {
                if ($assoc->info['advertiser_id'] == $request->identifier) {
                    $activeAssoc = $assoc;
                    break;
                }
            }

            collect($data)
                ->chunk($this->insert_chunk_size)
                ->each(function ($chunkDataRows) use ($request, $table, $activeAssoc) {
                    $toInsert = [];
                    foreach ($chunkDataRows as $dataRow) {
                        $toInsert[] = $this->processRecord($dataRow, $request, $activeAssoc);
                    }
                    
                    $this->insert($table, $toInsert);
                });

            return $totalItems;
        } catch (Exception $e) {
            // In case of exception, set update failed and rollBack DB
            Log::error('[TikTokDailyImporter] ' . $e->getMessage());
            throw $e;
        }
    }

    private function processRecord($row, ReportLogbook $request, $activeAssoc)
    {

        $date = Carbon::parse($row->dimensions->stat_time_day)->format('Y-m-d');

        $currency = $row->metrics->currency;

        $amount = $row->metrics->spend ?? 0;
        $amount_eur = $amount_usd = 0;

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

            'hash'                  => TikTokDailyReportData::getHash([
                $request->identifier, $row->dimensions->adgroup_id
            ]),

            'adgroup_id'            => $row->dimensions->adgroup_id,
            'adgroup_name'          => $row->metrics->adgroup_name,
            'conversion'            => $row->metrics->conversion,
            'campaign_id'           => $row->metrics->campaign_id,
            'campaign_name'         => $row->metrics->campaign_name,
            'real_time_cost_per_conversion' => $row->metrics->real_time_cost_per_conversion,
            'clicks'                => $row->metrics->clicks,
            'cost_per_conversion'   => $row->metrics->cost_per_conversion,
            'real_time_conversion'  => $row->metrics->real_time_conversion,
            'impressions'           => $row->metrics->impressions,
            'objective_type'        => $row->metrics->objective_type,

            'revenue_share'         => 1,
            'currency'              => $currency,
            'amount'                => $amount,
            'amount_eur' => $amount_eur,
            'amount_usd' => $amount_usd,

            'created_at' => $timestamp,
            'updated_at' => $timestamp
        ];


        return $record;
    }
}
