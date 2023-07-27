<?php

namespace App\Services\ARC\Sources\Providers\GoogleAds\Adperformance;

use App\Services\ARC\Sources\Abstracts\BaseImporter;
use App\Models\CurrencyConversion;
use App\Models\ARC\ReportLogbook;
use App\Models\ARC\Providers\GoogleAds\GoogleAdsAdperformanceReportData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\ClientArcAssociation;


use Exception;
use Illuminate\Support\Carbon as Carbon;
use Illuminate\Support\Facades\Storage as Storage;

/**
 * Class GoogleAdsAdperformanceImporter
 */
class GoogleAdsAdperformanceImporter extends BaseImporter
{

    public $send_created_event = false;

    public function doImport(ReportLogbook $request)
    {
        $identifier = $request->identifier;
        $table = $this->getReportTableName($request);

        Log::info("[GoogleAdsAdperformanceImporter] Start importing for identifier {$identifier}");

        try {
            $count = 0;
            // Read the CSV
            $localReport = $request->infoOriginalLocalReport;

            // Set read file and set header
            $json = json_decode(Storage::disk('system')->get($localReport));

            if (empty($json)) { // Note: depends from source
                Log::info("[GoogleAdsAdperformanceImporter] Empty data on {$localReport}");
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

        if ($currency === "EUR") {
            $amount_eur = $amount;
            $amount_usd = CurrencyConversion::convertAmount($row->segments->date, $currency, 'USD', $amount, 4, true);
        } else { //$currency === "USD") {
            $amount_usd = $amount;
            $amount_eur = CurrencyConversion::convertAmount($row->segments->date, $currency, 'EUR', $amount, 4, true);
        }
        $timestamp = Carbon::now();

        $device = strtolower($row->segments->device);
        $ad_group_labels = [];

        if (!empty($row->adGroup->labels)) {
            foreach ($row->adGroup->labels as $label) {
                $ad_group_labels[] = $label->name;
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
            'ad_group_status'       => $row->adGroup->status,
            'ad_group_cpc_bid'      => $row->adGroup->cpcBidMicros / 1000000,
            'ad_group_labels'       => json_encode($ad_group_labels),
            'ad_status'             => $row->adGroupAd->status,
            'ad_id'                 => $row->adGroupAd->ad->id,
            'ad_final_urls'         => json_encode($row->adGroupAd->ad->finalUrls ?? []),
            'campaign_id'           => $row->campaign->id,
            'campaign_name'         => $row->campaign->name,
            'campaign_status'       => $row->campaign->status,
            'campaign_budget_amount' => $row->campaign->campaignBudget->amountMicros / 1000000 ?? 0,
            'customer_id'           => $row->customer->id,
            'customer_name'         => $row->customer->descriptiveName,


            'keyword'               => '',
            'keyword_match_type'    => '',

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

            'hash'                  => GoogleAdsAdperformanceReportData::getHash([
                ($row->customer->id ?? ''),
                ($row->adGroupAd->ad->id ?? ''),
                ($row->segments->keyword->info->text ?? ''),
                ($row->segments->keyword->info->matchType ?? ''),
                $device,
            ])
        ];

        return $record;
    }
}
