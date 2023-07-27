<?php

namespace App\Services\ARC\Sources\Providers\GoogleAds\Campaigns;

use App\Services\ARC\Sources\Abstracts\BaseImporter;
use App\Models\CurrencyConversion;
use App\Models\ARC\ReportLogbook;
use App\Models\ARC\Providers\GoogleAds\GoogleAdsCampaignsReportData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Models\ClientArcAssociation;


use Exception;
use Illuminate\Support\Carbon as Carbon;
use Illuminate\Support\Facades\Storage as Storage;

/**
 * Class GoogleAdsCampaignsImporter
 */
class GoogleAdsCampaignsImporter extends BaseImporter
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

        $timestamp = Carbon::now();

        $campaign_labels = [];

        if (!empty($row->campaign->labels)) {
            foreach ($row->campaign->labels as $label) {
                $campaign_labels[] = $label->name;
            }
        }

        print_r($row);

        $record = [
            'date'                  => $request->date_end,
            'identifier'            => $request->identifier,

            'client_id'             => $activeAssoc->client_id,
            'market_id'             => $activeAssoc->market_id,
            'market'                => $activeAssoc->market->code,

            'campaign_id'           => $row->campaign->id,
            'campaign_name'         => $row->campaign->name,
            'campaign_status'       => $row->campaign->status,
            'campaign_labels'       => json_encode($campaign_labels),
            'campaign_budget_amount' => ($row->campaignBudget->amountMicros ?? 0)/ 1000000,

            'bidding_strategy'      => $row->campaign->biddingStrategyType ?? '',
            'maximize_conversions_target_cpa' => ($row->campaign->maximizeConversions->targetCpa ?? 0)/ 1000000 ,
            'maximize_conversions_value_target_roas' => ($row->campaign->maximizeConversionsValue->targetRoas ?? 0)/ 1000000,
            'target_cpa_target_cpa_amount' => ($row->campaign->targetCpa->targetCpaMicros ?? 0)/ 1000000,
            'target_roas_target_roas_amount' => ($row->campaign->targetRoas->targetRoas ?? 0)/ 1000000,
            'customer_id'           => $row->customer->id,
            'customer_name'         => $row->customer->descriptiveName,


            'created_at'            => $timestamp,
            'updated_at'            => $timestamp,

            'hash'                  => GoogleAdsCampaignsReportData::getHash([
                ($row->customer->id ?? ''),
                ($row->campaign->id ?? '')
            ])
        ];

        return $record;
    }
}
