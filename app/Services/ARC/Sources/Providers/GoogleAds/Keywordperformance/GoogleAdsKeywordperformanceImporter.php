<?php

namespace App\Services\ARC\Sources\Providers\GoogleAds\Keywordperformance;

use App\Services\ARC\Sources\Abstracts\BaseImporter;
use App\Models\CurrencyConversion;
use App\Models\ARC\ReportLogbook;
use App\Models\ARC\Providers\GoogleAds\GoogleAdsKeywordperformanceReportData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\ClientArcAssociation;



use Exception;
use Illuminate\Support\Carbon as Carbon;
use Illuminate\Support\Facades\Storage as Storage;

/**
 * Class GoogleAdsKeywordperformanceImporter
 */
class GoogleAdsKeywordperformanceImporter extends BaseImporter
{

    public $send_created_event = false;

    public function doImport(ReportLogbook $request)
    {
        $identifier = $request->identifier;
        $table = $this->getReportTableName($request);

        Log::info("[GoogleAdsKeywordperformanceImporter] Start importing for identifier {$identifier}");

        try {
            $count = 0;
            // Read the CSV
            $localReport = $request->infoOriginalLocalReport;

            // Set read file and set header
            $json = json_decode(Storage::disk('system')->get($localReport));

            if (empty($json)) { // Note: depends from source
                Log::info("[GoogleAdsKeywordperformanceImporter] Empty data on {$localReport}");
                return false;
            }

            // Clear data BEFORE importing new data
            $this->deleteData($request);

            $validAssociations = ClientArcAssociation::where('source', $this->source)
                ->inPeriod($request->date_end)
                ->get();

            $activeAssoc = null;
            foreach ($validAssociations as $assoc) {
                if ($assoc->info['customer_id'] == $request->identifier) {
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
        $currency = strtoupper($row->customer->currencyCode);

        $amount = $row->metrics->costMicros / 1000000 ?? 0;
        $amount_eur = $amount_usd = 0;

        
            $amount_usd = CurrencyConversion::convertAmount($row->segments->date, $currency, 'USD', $amount, 4, true);
            $amount_eur = CurrencyConversion::convertAmount($row->segments->date, $currency, 'EUR', $amount, 4, true);
        $timestamp = Carbon::now();

        $device = strtolower($row->segments->device);
        // $ad_group_labels = [];
        $keyword_labels = [];

        // if (!empty($row->adGroup->labels)) {
        //     foreach ($row->adGroup->labels as $label) {
        //         $ad_group_labels[] = $label->name;
        //     }
        // }


        if (!empty($row->adGroupCriterion->labels)) {
            foreach ($row->adGroupCriterion->labels as $label) {
                $keyword_labels[] = $label->name;
            }
        }

        $record = [
            'date'                  => $row->segments->date,
            'identifier'            => $request->identifier,

            'client_id'             => $activeAssoc->client_id,
            'market_id'             => $activeAssoc->market_id,
            'market'                => $activeAssoc->market->code,

            'device'                => $device,

            'ad_group_id'           => $row->adGroup->id,
            'ad_group_name'         => $row->adGroup->name,
            'ad_group_cpc_bid'      => $row->adGroup->cpcBidMicros / 1000000,
            'campaign_id'           => $row->campaign->id,
            'campaign_name'         => $row->campaign->name,
            'customer_id'           => $row->customer->id,
            'customer_name'         => $row->customer->descriptiveName,


            'keyword'               => $row->adGroupCriterion->keyword->text ?? '',
            'keyword_match_type'    => $row->adGroupCriterion->keyword->matchType ?? '',

            'keyword_status'        => $row->adGroupCriterion->status,
            'keyword_labels'        => json_encode($keyword_labels),

            'clicks'                => $row->metrics->clicks ?? 0,
            'conversions'           => $row->metrics->conversions ?? 0,
            'ctr'                   => $row->metrics->ctr ?? 0,
            'impressions'           => $row->metrics->impressions ?? 0,
            'video_views'           => $row->metrics->videoViews ?? 0,

            'currency'              => $currency,
            'revenue_share'         => 1,
            'amount'                => $amount,
            'amount_eur'            => $amount_eur,
            'amount_usd'            => $amount_usd,

            'created_at'            => $timestamp,
            'updated_at'            => $timestamp,

            'hash'                  => GoogleAdsKeywordperformanceReportData::getHash([
                ($row->customer->id ?? ''),
                ($row->campaign->id ?? ''),
                ($row->adGroup->id ?? ''),
                ($row->adGroupCriterion->keyword->text ?? ''),
                ($row->adGroupCriterion->keyword->matchType ?? ''),
                $device,
            ])
        ];

        return $record;
    }
}
