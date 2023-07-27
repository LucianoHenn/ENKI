<?php

namespace App\Services\ARC\Sources\Providers\Zemanta\Daily;

use App\Services\ARC\Sources\Abstracts\BaseImporter;
use App\Models\CurrencyConversion;
use App\Models\ARC\ReportLogbook;
use App\Models\ARC\Providers\Zemanta\ZemantaDailyReportData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// use League\Csv\Statement;
// use League\Csv\Reader;

use Exception;
use Illuminate\Support\Carbon as Carbon;
use Illuminate\Support\Facades\Storage as Storage;
use App\Models\ClientArcAssociation;

/**
 * Class ZemantaDailyImporter
 */
class ZemantaDailyImporter extends BaseImporter
{

    public $send_created_event = false;
    public $insert_chunk_size = 10000;
    public function doImport(ReportLogbook $request)
    {
        $identifier = $request->identifier;
        $table = $this->getReportTableName($request);

        Log::info("[ZemantaDailyImporter] Start importing for identifier {$identifier}");

        try {
            $startProcess = hrtime(true);
            $count = 0;
            // Read the CSV
            $localReport = $request->infoOriginalLocalReport;

            // Set read file and set header
            $data = json_decode(file_get_contents($localReport), true);
            // $csv = Reader::createFromPath($localReport, 'r');
            // $csv->setHeaderOffset(0);

            // Check CSV size
            //$count = $csv->count();
            $count = count($data);
            if ($count <= 0) { // Note: depends from source
                Log::info("[ZemantaDailyImporter] Empty data on {$localReport}");
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

            

            // $stmt = (new Statement())->offset(0)->limit(count($csv) - 1);
            // $csvRows = $stmt->process($csv);

            // $range =
            //     $csv->count() >= $this->insert_chunk_size ?
            //     range(0, $csv->count(), $this->insert_chunk_size) :
            //     [0];
            
            Log::info("[ZemantaDailyImporter] Iterating through {$count} records on, json file {$localReport}");
            $insertOperationZero = 0;
            collect($data)
                ->chunk($this->insert_chunk_size)
                ->each(function ($chunkCsvRows) use ($request, $table, $activeAssoc, $count, &$insertOperationZero) {
                    // $toInsert = collect($chunkCsvRows)
                    //     ->filter(fn ($csvRow) => $this->filterRecord($csvRow, $request))
                    //     ->map(fn ($csvRow) => $this->processRecord($csvRow, $request, $activeAssoc))
                    //     ->toArray();
                    $toInsert = [];
                    //dump('Filtering ... block #' .$insertOperationZero);
                    foreach ($chunkCsvRows as $csvRow) {
                        $record = $this->processRecord($csvRow, $request, $activeAssoc);
                        if (!empty($record)) {
                            $toInsert[] = $record;
                        }
                    }
                    //dump('Filtered ... block #' .$insertOperationZero);
                    if (count($toInsert) != 0) {
                        $start = hrtime(true);
                        Log::info('[ZemantaDailyImporter] Loading ... block #' .$insertOperationZero);
                        $this->insert(
                            $table,
                            
                            $toInsert
                        );
                        //dump($sql);
                        
                        $end = hrtime(true);
                        $eta = $end - $start;
                        $eta = round($eta / 1e+9, 2);
                        $insertOperationZero += count($toInsert);
                        $msg = "[ZemantaImporter] Loaded (sec $eta): " . ($insertOperationZero) . " of {$count} computed, inserting chunk of " . $this->insert_chunk_size . " records";
                        //dump($msg);
                        Log::debug($msg);
                    }
                });
            //used to free memory

            unset($csv);

            $endProcess = hrtime(true);
            $etaProcess = $endProcess - $startProcess;
            $etaProcess = $etaProcess / 1e+9;
            Log::info("[ZemantaImporter] All datas imported [{$count} // sec {$etaProcess}] for {$this->source} {$identifier} {$request->date_end}");
            // ---


            return $count;
        } catch (Exception $e) {
            // In case of exception, set update failed and rollBack DB
            throw $e;
        }
    }



    private function filterRecord($row, ReportLogbook $request)
    {
        return floatval($row['Total Spend']) > 0;
    }

    private function processRecord($row, ReportLogbook $request, ClientArcAssociation $activeAssoc)
    {
        
        if (!$this->filterRecord($row, $request)) return null;
        $amount_eur = $row['Currency'] === 'EUR' ? $row['Total Spend'] : CurrencyConversion::convertAmount($request->date_end, $row['Currency'], 'EUR', $row['Total Spend'], 4, true);

        $amount_usd = $row['Currency'] === 'USD' ? $row['Total Spend'] : CurrencyConversion::convertAmount($request->date_end, $row['Currency'], 'USD', $row['Total Spend'], 4, true);

        $timestamp = Carbon::now();


        $record = [
            'date'                => date('Y-m-d', strtotime($row['Day'])),
            'identifier'            => $request->identifier,

            'client_id'             => $activeAssoc->client_id,
            'market_id'             => $activeAssoc->market_id,
            'market'                => $activeAssoc->market->code,
            'hash'                => ZemantaDailyReportData::getHash([

                $row['Account Id'],
                $row['Campaign Id'],
                $row['Ad Group Id'] ?? '',
                $row['Content Ad Id'] ?? '',
                $row['Placement'] ?? '',
                $row['Publisher'] ?? '',
                $row['Media Source'] ?? '',
                $row['URL'] ?? '',
                microtime(true),
                mt_rand(),
            ]),

            'account'                               => $row['Account'],
            'account_id'                            => $row['Account Id'],
            'campaign'                              => $row['Campaign'] ?? '',
            'campaign_id'                           => $row['Campaign Id'] ?? 0,
            'ad_group'                              => $row['Ad Group'] ?? '',
            'ad_group_id'                           => $row['Ad Group Id'] ?? 0,
            'content_ad'                            => $row['Content Ad'] ?? '',
            'content_ad_id'                         => $row['Content Ad Id'] ?? 0,
            'publisher'                             => $row['Publisher'] ?? '',
            'mediasource_id'                        => $row['Media Source Id'] ?? 0,
            'mediasource'                           => $row['Media Source'] ?? '',
            'mediasource_slug'                      => $row['Media Source Slug'] ?? '',
            'placement'                             => $row['Placement'] ?? '',

            'impressions'                           => $row['Impressions'],
            'clicks'                                => $row['Clicks'],
            'ctr'                                   => $row['CTR']                                      ?: 0,
            'avg_cpc'                               => $row['Avg. CPC']                                 ?: 0,
            'avg_cpm'                               => $row['Avg. CPM']                                 ?: 0,
            'yesterday_spend'                       => $row['Yesterday Spend']                          ?: 0,
            'media_spend'                           => $row['Media Spend']                              ?: 0,
            'data_cost'                             => $row['Data Cost']                                ?: 0,
            'license_fee'                           => $row['License Fee']                              ?: 0,
            'total_spend'                           => $row['Total Spend']                              ?: 0,
            'margin'                                => $row['Margin']                                   ?: 0,
            'visits'                                => $row['Visits']                                   ?: 0,
            'unique_users'                          => $row['Unique Users']                             ?: 0,
            'new_users'                             => $row['New Users']                                ?: 0,
            'returning_users'                       => $row['Returning Users']                          ?: 0,
            'perc_new_users'                        => $row['% New Users']                              ?: 0,
            'pageviews'                             => $row['Pageviews']                                ?: 0,
            'pageviews_per_visit'                   => $row['Pageviews per Visit']                      ?: 0,
            'bounced_visits'                        => $row['Bounced Visits']                           ?: 0,
            'nonbounced_visits'                     => $row['Non-Bounced Visits']                       ?: 0,
            'bounce_rate'                           => $row['Bounce Rate']                              ?: 0,
            'total_seconds'                         => $row['Total Seconds']                            ?: 0,
            'time_on_site'                          => $row['Time on Site'],
            'avg_cost_per_visit'                    => $row['Avg. Cost per Visit']                      ? $this->getFloatVal($row['Avg. Cost per Visit']) : 0,
            'avg_cost_per_new_visitor'              => $row['Avg. Cost per New Visitor']                ? $this->getFloatVal($row['Avg. Cost per New Visitor']) : 0,
            'avg_cost_per_pageview'                 => $row['Avg. Cost per Pageview']                   ? $this->getFloatVal($row['Avg. Cost per Pageview']) : 0,
            'avg_cost_per_nonbounced_visit'         => $row['Avg. Cost per Non-Bounced Visit']          ? $this->getFloatVal($row['Avg. Cost per Non-Bounced Visit']) : 0,
            'avg_cost_per_minute'                   => $row['Avg. Cost per Minute']                     ? $this->getFloatVal($row['Avg. Cost per Minute']) : 0,
            'avg_cost_per_unique_user'              => $row['Avg. Cost per Unique User']                ? $this->getFloatVal($row['Avg. Cost per Unique User']) : 0,
            'account_status'                        => $row['Account Status'],
            'campaign_status'                       => $row['Campaign Status'],
            'ad_group_status'                       => $row['Ad Group Status'],
            'content_ad_status'                     => $row['Content Ad Status'],
            'media_source_status'                   => $row['Media Source Status'],
            'publisher_status'                      => $row['Publisher Status'],
            'video_start'                           => $row['Video Start'],
            'video_first_quartile'                  => $row['Video First Quartile'],
            'video_midpoint'                        => $row['Video Midpoint'],
            'video_third_quartile'                  => $row['Video Third Quartile'],
            'video_complete'                        => $row['Video Complete'],
            'video_progress_3s'                     => $row['Video Progress 3s'],
            'avg_cpv'                               => $row['Avg. CPV']                                 ? $this->getFloatVal($row['Avg. CPV']) : 0,
            'avg_cpcv'                              => $row['Avg. CPCV']                                ? $this->getFloatVal($row['Avg. CPCV']) : 0,
            'measurable_impressions'                => $row['Measurable Impressions']                   ? $this->getIntVal($row['Measurable Impressions']) : 0,
            'viewable_impressions'                  => $row['Viewable Impressions']                     ? $this->getIntVal($row['Viewable Impressions']) : 0,
            'notmeasurable_impressions'             => $row['Not-Measurable Impressions']               ? $this->getIntVal($row['Not-Measurable Impressions']) : 0,
            'notviewable_impressions'               => $row['Not-Viewable Impressions']                 ? $this->getIntVal($row['Not-Viewable Impressions']) : 0,
            'perc_measurable_impressions'           => $row['% Measurable Impressions']                 ? $this->getFloatVal($row['% Measurable Impressions']) : 0,
            'perc_viewable_impressions'             => $row['% Viewable Impressions']                   ? $this->getFloatVal($row['% Viewable Impressions']) : 0,
            'impression_distribution_viewable'      => $row['Impression Distribution (Viewable)']       ? $this->getFloatVal($row['Impression Distribution (Viewable)']) : 0,
            'impression_distribution_notmeasurable' => $row['Impression Distribution (Not-Measurable)'] ? $this->getFloatVal($row['Impression Distribution (Not-Measurable)']) : 0,
            'impression_distribution_notviewable'   => $row['Impression Distribution (Not-Viewable)']   ? $this->getFloatVal($row['Impression Distribution (Not-Viewable)']) : 0,
            'avg_vcpm'                              => $row['Avg. VCPM']                                ? $this->getFloatVal($row['Avg. VCPM']) : 0,
            'conversions'                           => !empty($row['conv - Click attr.']) ? $row['conv - Click attr.'] : 0,
            'conversions_view'                      => !empty($row['conv - View attr.']) ? $row['conv - View attr.'] : 0,

            'revenue_share' => 1,
            'currency'   => $row['Currency'],
            'amount'       => $row['Total Spend'],
            'amount_eur'   => $amount_eur,
            'amount_usd'   => $amount_usd,

            'url'            => $row['URL']            ?? '',
            'display_url'    => $row['Display URL']    ?? '',
            'brand_name'     => $row['Brand Name']     ?? '',
            'description'    => $row['Description']    ?? '',
            'image_hash'     => $row['Image Hash']     ?? '',
            'image_url'      => $row['Image URL']      ?? '',
            'call_to_action' => $row['Call to action'] ?? '',
            'label'          => $row['Label']          ?? '',
            'uploaded'       => $row['Uploaded']       ?? '',
            'batch_name'     => $row['Batch Name']     ?? '',

            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ];

        return $record;
    }

    public function getFloatVal($input)
    {
        return (float) filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }

    public function getIntVal($input)
    {
        return intVal($this->getFloatVal($input));
    }
}
