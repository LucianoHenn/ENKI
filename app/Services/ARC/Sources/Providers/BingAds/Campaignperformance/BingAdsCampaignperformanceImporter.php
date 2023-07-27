<?php

namespace App\Services\ARC\Sources\Providers\BingAds\Campaignperformance;

use App\Services\ARC\Sources\Abstracts\BaseImporter;
use App\Models\CurrencyConversion;
use App\Models\ARC\ReportLogbook;
use App\Models\ARC\Providers\BingAds\BingAdsCampaignperformanceReportData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use League\Csv\Reader;
use League\Csv\Statement;

use Exception;
use Illuminate\Support\Carbon as Carbon;
use Illuminate\Support\Facades\Storage as Storage;

use App\Models\ClientArcAssociation;
/**
 * Class BingAdsCampaignperformanceImporter
 */
class BingAdsCampaignperformanceImporter extends BaseImporter
{

    public $send_created_event = false;

    public function doImport(ReportLogbook $request)
    {
        $identifier = $request->identifier;
        $table = $this->getReportTableName($request);

        Log::info("[BingAdsCampaignperformanceImporter] Start importing for identifier {$identifier}");

        try {
            $count = 0;
            // Read the CSV
            $localReport = $request->infoOriginalLocalReport;

            // Set read file and set header
            $csv = Reader::createFromPath($localReport, 'r');

            $csv->setHeaderOffset(10);

            // Check CSV size
            if (count($csv) <= 1) { // Note: depends from source
                Log::info("[BingAdsCampaignperformanceImporter] Empty data on {$localReport}");
                return false;
            }

            // Clear data BEFORE importing new data
            $this->deleteData($request);


            // Prepare rows to iterate

            $stmt = (new Statement())->offset(9)->limit(count($csv) - 10);
            $csvRows = $stmt->process($csv);

            \Log::info("[BingAdsCampaignperformanceImporter] Iterating through CSV file {$localReport}");

            // If everything went well, start inserting data
            $count = 0;

            $validAssociations = ClientArcAssociation::where('source', $this->source)
            ->inPeriod($request->date_end)
            ->get();

            $activeAssoc = null;
            foreach($validAssociations as $assoc) {
                if($assoc->info['account_number'] == $request->identifier) {
                    $activeAssoc = $assoc;
                    break;
                }
            }

            collect($csvRows)->chunk($this->insert_chunk_size)->each(function ($chunkCsvRows) use ($request, $table, $activeAssoc) {
                $toInsert = [];
                foreach ($chunkCsvRows as $csvRow) {
                    $toInsert[] = $this->processRecord($csvRow, $request, $activeAssoc);
                }

                // Can we use also a factory method
                $this->insert($table, $toInsert);
            });

            $count = $csvRows->count();

            \Log::info("[BingAdsCampaignperformanceImporter] All datas imported for {$this->source} {$identifier} {$request->date_end}: {$count} Rows");
            return $count;
        } catch (Exception $e) {
            // In case of exception, set update failed and rollBack DB
            throw $e;
        }
    }

    private function processRecord($row, ReportLogbook $request, ClientArcAssociation $activeAssoc)
    {

        $currency = strtoupper($row['CurrencyCode']);

        $amount = $row['Spend'] ?? 0;
        $amount_eur = $amount_usd = 0;

        if ($currency === "EUR") {
            $amount_eur = $amount;
            $amount_usd = CurrencyConversion::convertAmount($row['TimePeriod'], $currency, 'USD', $amount, 4, true);
        } else { //$currency === "USD") {
            $amount_usd = $amount;
            $amount_eur = CurrencyConversion::convertAmount($row['TimePeriod'], $currency, 'EUR', $amount, 4, true);
        }


        $device = strtolower($row['DeviceType']);


        $timestamp = Carbon::now();

        $record = [
            'date'          => $row['TimePeriod'],
            'identifier'    => $request->identifier,

            'client_id'     => $activeAssoc->client_id,
            'market_id'     => $activeAssoc->market_id,
            'market'        => $activeAssoc->market->code,

            'account_name'  => $row['AccountName'],
            'account_id'    => $row['AccountId'],
            'account_number'    => $row['AccountNumber'],

            'campaign_id' => $row['CampaignId'],
            'campaign_name'    => $row['CampaignName'],
            'campaign_type'    => $row['CampaignType'],

            'ad_distribution'     => $row['AdDistribution'] ?? '',
            'bid_match_type'     => $row['BidMatchType'] ?? '',
            'delivered_match_type'     => $row['DeliveredMatchType'] ?? '',
            'device'      => $device,
            'network'     => $row['Network'] ?? '',

            'budget'     => $row['BudgetName'] ?? '',
            'budget_status'     => $row['BudgetStatus'] ?? '',
            'budget_association_status'     => $row['BudgetAssociationStatus'] ?? '',

            'clicks'      => $row['Clicks'],
            'impressions' => $row['Impressions'],
            'conversions' => $row['Conversions'],

            'avg_cpc'     => !empty($row['AverageCpc']) ? $row['AverageCpc'] : 0,

            'quality_score'     => (!empty($row['QualityScore']) && is_numeric($row['QualityScore'])) ? $row['QualityScore'] :  0,
            'ctr'           => !empty($row['Ctr']) ? str_replace('%', '', $row['Ctr']) : 0,
            'avg_pos'       => !empty($row['AveragePosition']) ? $row['AveragePosition'] : 0,

            'labels'    => $row['CampaignLabels'] ?? '',
            'status'    => $row['CampaignStatus'] ?? '',

            'currency'    => $currency,
            'revenue_share' => 1,
            'amount'        => $amount,
            'amount_eur'    => $amount_eur,
            'amount_usd'    => $amount_usd,

            'created_at' => $timestamp,
            'updated_at' => $timestamp,

            'hash'      => BingAdsCampaignperformanceReportData::getHash([
             ($row['AccountId'] ?? ''),
                ($row['CampaignId'] ?? ''),
                ($row['AdDistribution'] ?? ''),
                ($row['BidMatchType'] ?? ''),
                ($row['DeliveredMatchType'] ?? ''),
                $device,
                ($row['Network'] ?? '')
            ])
        ];

        return $record;
    }
}
