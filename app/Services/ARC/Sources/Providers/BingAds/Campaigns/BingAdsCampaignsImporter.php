<?php

namespace App\Services\ARC\Sources\Providers\BingAds\Campaigns;

use App\Services\ARC\Sources\Abstracts\BaseImporter;
use App\Models\CurrencyConversion;
use App\Models\ARC\ReportLogbook;
use App\Models\ARC\Providers\BingAds\BingAdsCampaignsReportData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use League\Csv\Statement;
use League\Csv\Reader;

use Exception;
use Illuminate\Support\Carbon as Carbon;
use Illuminate\Support\Facades\Storage as Storage;
use App\Models\ClientArcAssociation;

/**
 * Class BingAdsCampaignsImporter
 */
class BingAdsCampaignsImporter extends BaseImporter
{

    public $send_created_event = false;

    public function doImport(ReportLogbook $request)
    {
        $identifier = $request->identifier;
        $table = $this->getReportTableName($request);

        Log::info("[BingAdsCampaignsImporter] Start importing for identifier {$identifier}");

        try {
            $count = 0;
            // Read the CSV
            $localReport = $request->infoOriginalLocalReport;

            $data = json_decode(Storage::disk('system')->get($localReport));

            // Check CSV size
            if (empty($data->Campaigns->Campaign)) { // Note: depends from source
                Log::info("[BingAdsCampaignsImporter] Empty data on {$localReport}");
                return false;
            }

            // Clear data BEFORE importing new data
            $this->deleteData($request);

            \Log::info("[BingAdsCampaignsImporter] Iterating through CSV file {$localReport}");

            // If everything went well, start inserting data
            $count = 0;

            $validAssociations = ClientArcAssociation::where('source', $this->source)
                ->inPeriod($request->date_end)
                ->get();

            $activeAssoc = null;
            foreach ($validAssociations as $assoc) {
                if ($assoc->info['account_number'] == $request->identifier) {
                    $activeAssoc = $assoc;
                    break;
                }
            }

            collect($data->Campaigns->Campaign)->chunk($this->insert_chunk_size)->each(function ($chunkCsvRows) use ($request, $table, $activeAssoc) {
                $toInsert = [];
                foreach ($chunkCsvRows as $csvRow) {
                    $toInsert[] = $this->processRecord($csvRow, $request, $activeAssoc);
                }

                // Can we use also a factory method
                $this->insert($table, $toInsert);
            });

            $count = count($data->Campaigns->Campaign);

            \Log::info("[BingAdsCampaignsImporter] All datas imported for {$this->source} {$identifier} {$request->date_end}: {$count} Rows");
            return $count;
        } catch (Exception $e) {
            // In case of exception, set update failed and rollBack DB
            throw $e;
        }
    }

    private function processRecord($row, ReportLogbook $request, ClientArcAssociation $activeAssoc)
    {

        $timestamp = Carbon::now();

        $record = [
            'date'          => $request->date_end,
            'identifier'    => $request->identifier,

            'client_id'     => $activeAssoc->client_id,
            'market_id'     => $activeAssoc->market_id,
            'market'        => $activeAssoc->market->code,

            'campaign_id'   => $row->Id,
            'campaign_name' => $row->Name,

            'audience_ads_bid_adjustment' => $row->AudienceAdsBidAdjustment ?? 0,
            'bidding_scheme_type' => $row->BiddingScheme->Type ?? 0,
            'bidding_scheme_max_cpc' => $row->BiddingScheme->MaxCpc->Amount ?? 0,
            'bidding_scheme_target_cpa' => $row->BiddingScheme->TargetCpa ?? 0,
            'budget_type' => $row->BudgetType ?? '',
            'daily_budget' => $row->DailyBudget ?? 0,
            'experiment_id' => $row->ExperimentId ?? 0,
            'final_url_suffix'  => $row->FinalUrlSuffix ?? '',


            'campaign_type'   => $row->CampaignType ?? '',
            'sub_type' => $row->SubType ?? '',

            'time_zone'  => $row->TimeZone ?? '',
            'tracking_url_template'   => $row->TrackingUrlTemplate ?? '',
            'url_custom_parameters'   => json_encode($row->UrlCustomParameters ?? ''),

            'settings'   => json_encode($row->Settings ?? ''),

            'budget_id' => $row->BudgetId ?? '',
            'languages'   => json_encode($row->Languages ?? ''),
            'status' => $row->Status ?? '',

            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ];

        return $record;
    }
}
