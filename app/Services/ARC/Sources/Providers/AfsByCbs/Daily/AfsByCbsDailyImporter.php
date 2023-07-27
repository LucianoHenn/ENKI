<?php

namespace App\Services\ARC\Sources\Providers\AfsByCbs\Daily;

use App\Services\ARC\Sources\Abstracts\BaseImporter;
use App\Models\CurrencyConversion;
use App\Models\ARC\ReportLogbook;
use App\Models\ARC\Providers\AfsByCbsDailyReportData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


use Exception;
use Illuminate\Support\Carbon as Carbon;
use Illuminate\Support\Facades\Storage as Storage;
use League\Csv\Statement;
use League\Csv\Reader;

use App\Models\Semberry\ChannelTracer;
use App\Models\Semberry\Conversion;

use App\Models\ClientArcAssociation;
use App\Models\Country;

/**
 * Class AfsByCbsDailyImporter
 */
class AfsByCbsDailyImporter extends BaseImporter
{

    public $send_created_event = false;
    public $currency = 'USD';
    public $desktop_revenue_share = 0.675;
    public $tablet_revenue_share = 0.25;
    public $mobile_revenue_share = 0.25;
    private $country_code_array = [];

    // protected $channelTracers;

    public function doImport(ReportLogbook $request)
    {
        $identifier = $request->identifier;
        // $table = $this->getReportTableName($request);


        Log::info("[AfsByCbsDailyImporter] Start importing for identifier {$identifier}");

        try {
            // $this->channelTracers = ChannelTracer::where('DATE', '>=', $request->date_begin)
            //     ->where('DATE', '<=', $request->date_end)->get();

            $count = 0;
            // Read the CSV
            $localReport = $request->infoOriginalLocalReport;

            // Set read file and set header
            $csv = Reader::createFromPath($localReport, 'r');
            $csv->setHeaderOffset(0);

            // Check CSV size
            if ($csv->count() <= 0) { // Note: depends from source
                Log::info("[AfsByCbsDailyImporter] Empty data on {$localReport}");
                return false;
            }

            // Clear data BEFORE importing new data
            try {
                $this->deleteData($request);
            } catch (\Exception $e) {
                //do nothing
                Log::warning('[AfsByCbsDailyImporter]' . $e->getMessage());
            }

            $stmt = (new Statement())->offset(1)->limit($csv->count() - 2);
            $csvRows = $stmt->process($csv);


            //we need to pack data by date
            $dateChunks = [];
            foreach ($csvRows as $row) {
                $row = (object)array_change_key_case($row, CASE_LOWER);

                if (!isset($row->estimated_earnings)) {
                    $row->estimated_earnings = 0;
                }


                if (!isset($dateChunks[$row->date])) {
                    $dateChunks[$row->date] = [];
                }
                $k = implode('__', [
                    $row->ad_client_id, $row->custom_channel_name, $row->platform_type_name,$row->country_name
                ]);
                if (!isset($dateChunks[$row->date][$k])) {
                    $dateChunks[$row->date][$k] = $row;
                } else {
                    $dateChunks[$row->date][$k]->clicks += $row->clicks;
                    $dateChunks[$row->date][$k]->estimated_earnings += $row->estimated_earnings ?? 0;
                    $dateChunks[$row->date][$k]->page_views += $row->page_views;
                    $dateChunks[$row->date][$k]->impressions += $row->impressions;
                }
            }

            //print_r($dateChunks);

            foreach ($dateChunks as $dt => $dtChunk) {
                $validAssociations = ClientArcAssociation::where('source', $this->source)
                    ->inPeriod($dt)
                    ->get();

                $activeAssoc = null;
                foreach ($validAssociations as $assoc) {
                    if ($assoc->info['site_id'] == $request->identifier) {
                        $activeAssoc = $assoc;
                        break;
                    }
                }
                list($req, $tbl) = $this->retriveDateRequestAndTable($dt, $request);
                //set table

                collect($dtChunk)
                    ->chunk($this->insert_chunk_size)
                    ->each(function ($chunkDataRows) use ($request, $tbl, $dt, $identifier, $activeAssoc,  &$count) {
                        $toInsert = [];
                        foreach ($chunkDataRows as $dataRow) {
                            $records = $this->processRecord($dataRow, $request, $activeAssoc);

                            // if($dataRow->date == '2023-05-16' && $dataRow->custom_channel_name == 'T0000115' && $dataRow->ad_client_id == 'partner-helpwire-content-2' && $dataRow->country_name = 'United Kingdom') {
                            //     print_r($records);
                            // }
                            $toInsert = array_merge($toInsert, $records);
                            $count += count($records);
                        }

                        try {
                            //print_r($tbl."\n");
                            $this->insert(
                                $tbl,

                                $toInsert
                            );
                        } catch (\Exception $e) {
                            Log::warning('[AfsByCbsDailyImporter] doImport, insert Exception: ' . $e->getMessage());
                        }
                    });
            }

            return $count;
        } catch (Exception $e) {
            // In case of exception, set update failed and rollBack DB
            throw $e;
        }
    }

    public function getCountryCode($country)
    {
        if (array_key_exists($country, $this->country_code_array))
            return $this->country_code_array[$country];
        $foundCountry = Country::whereRaw('LOWER(name) = LOWER(?)', [$country])->first();
        if ($foundCountry)
            $this->country_code_array[$country] =     $foundCountry;
        return $foundCountry;
    }

    protected function processRecord($csvRow, ReportLogbook $request, ClientArcAssociation $activeAssoc)
    {
        $records = [];

        $country = $this->getCountryCode($csvRow->country_name);
        $countryCode = $country ? $country->code : null;

        if($countryCode == 'gb') $countryCode = 'uk';





        $channel = null;
        
        // check if there is only one country for the given channel
        $cnt = ChannelTracer::selectRaw('COUNTRY, count(*) as cnt')->where('DATE', $csvRow->date)
        ->where('CHANNEL', $csvRow->custom_channel_name)
        ->where('WEBSITE', $csvRow->ad_client_id)
        ->groupBy('COUNTRY')->get()->toArray();


        // if($csvRow->custom_channel_name == 'T0000115' && $csvRow->ad_client_id == 'partner-helpwire-content-2') {
        //     print_r($cnt);
        // }

        // if($csvRow->date == '2023-05-16' && $csvRow->custom_channel_name == 'T0000115' && $csvRow->ad_client_id == 'partner-helpwire-content-2' && strtoupper($countryCode) == 'UK') {
        //     print_r($csvRow);
        // }




        if(count($cnt) > 0) {
            if(count($cnt) == 1) {
                $channel = ChannelTracer::where('DATE', $csvRow->date)
                ->where('CHANNEL', $csvRow->custom_channel_name)
                ->where('WEBSITE', $csvRow->ad_client_id);
                
                $channel = $channel->first();
            } else {
                if (!is_null($countryCode)) {
                    
                    $channel = ChannelTracer::where('DATE', $csvRow->date)
                        ->where('CHANNEL', $csvRow->custom_channel_name)
                        ->where('WEBSITE', $csvRow->ad_client_id)
                        ->where("COUNTRY", strtoupper($countryCode));
                    $channel = $channel->first();

                    //if($csvRow->custom_channel_name == 'T0000115' && $csvRow->ad_client_id == 'partner-helpwire-content-2' && strtoupper($countryCode) == 'UK') print_r($channel);
                    
                } else {
                    Log::info("[AfsByCbsDailyImporter][CountryCode not found] Data: { country: '{$csvRow->country_name}', date: '{$csvRow->date}', channel : '{$csvRow->custom_channel_name}' ,website: '{$csvRow->ad_client_id}' }");
                }
            }
        }
        


        if (is_null($channel)) {
            Log::info("[AfsByCbsDailyImporter][Channel not found] Data: { countryCode: '{$countryCode}', date: '{$csvRow->date}', channel : '{$csvRow->custom_channel_name}' ,website: '{$csvRow->ad_client_id}' }");

            $settings = (object) [
                'channel' => $csvRow->custom_channel_name,
                'keyword' => 'none',
                'campaign' => 'unk-' . $csvRow->custom_channel_name . $csvRow->ad_client_id,
                'campaign_id' => 'unk-' . $csvRow->custom_channel_name . $csvRow->ad_client_id,
                'source' => 'unknown',
                'country' => strtolower($countryCode) ?? 'xx',
            ];
        } else {
            $settings = (object) [
                'channel' => $channel->CHANNEL,
                'keyword' => $channel->KEYWORD,
                'campaign' => $channel->CAMPAIGN,
                'campaign_id' => $channel->CAMPAIGN_ID,
                'source' => $channel->SOURCE,
                'country' => $channel->COUNTRY,
            ];
        }


        $convBuilder = Conversion::selectRaw('SEARCHED_KEYWORD as keyword_in, BIDMATCH as bidmatch, count(*) as conversions')
            ->where('DATE', $csvRow->date)
            ->where('CHANNEL', $csvRow->custom_channel_name)
            ->where('WEBSITE', $csvRow->ad_client_id);

        if (!is_null($channel)) {
            $convBuilder->where('CAMPAIGN', $channel->CAMPAIGN);
        }

        $conversions = $convBuilder->groupByRaw('SEARCHED_KEYWORD,BIDMATCH')->get();

        if ($conversions->count() == 0) {
            if ($csvRow->clicks == 0) return [];
            $conversions = [];
            $conversions[] = (object)[
                'keyword_in' => $settings->keyword,
                'bidmatch' => 'e',
                'conversions' => $csvRow->clicks
            ];
        }



        $total_conversions = 0;
        foreach ($conversions as $cc) {

            $total_conversions += $cc->conversions;
        }


        foreach ($conversions as $cc) {
            $coef = $cc->conversions / $total_conversions;
            $amount         = ($csvRow->estimated_earnings ?? 0) * $coef  ?? 0;
            $clicks         = floor($csvRow->clicks * $coef  ?? 0);
            $page_views     = floor($csvRow->page_views * $coef  ?? 0);
            $impressions    = floor($csvRow->impressions * $coef  ?? 0);

            $amount_eur = $amount_usd = 0;

            if ($this->currency === "EUR") {
                $amount_eur = $amount;
                $amount_usd = CurrencyConversion::convertAmount($csvRow->date, $this->currency, 'USD', $amount, 4, true);
            } else { //$currency === "USD") {
                $amount_usd = $amount;
                $amount_eur = CurrencyConversion::convertAmount($csvRow->date, $this->currency, 'EUR', $amount, 4, true);
            }
            $rev_share = $this->desktop_revenue_share;
            if ($csvRow->platform_type_name == 'Tablets') {
                $rev_share = $this->tablet_revenue_share;
            } elseif ($csvRow->platform_type_name == 'High-end mobile devices') {
                $rev_share = $this->mobile_revenue_share;
            }


            // if(intVal(str_replace('-', '', $csvRow->date)) >= 20220721) {
            //     $rev_share = 0.65; 
            // }

            $timestamp = now();
            $records[] = [
                'date'          => $csvRow->date,
                'identifier'    => $request->identifier,
                'client_id'     => $activeAssoc->client_id,
                'channel'       => $csvRow->custom_channel_name,
                'ad_client_id'  => $csvRow->ad_client_id,
                'keyword'       => $settings->keyword,
                'keyword_in'    => $cc->keyword_in,
                'bidmatch'      => $cc->bidmatch,
                'conversions'   => $cc->conversions,
                'campaign'      => $settings->campaign,
                'campaign_id'   => $settings->campaign_id,
                'in_source'     => $settings->source,
                'country'       => $settings->country,
                'platform_type_name' => $csvRow->platform_type_name,

                'hash'          => AfsByCbsDailyReportData::getHash([
                    $csvRow->ad_client_id,
                    $csvRow->country_name,
                    $csvRow->custom_channel_name,
                    $csvRow->platform_type_name,
                    $settings->keyword,
                    $cc->keyword_in,
                    $cc->bidmatch,
                    $settings->campaign,
                    $settings->campaign_id,
                    $settings->source
                ]),

                'estimated_page_views'  => $page_views,
                'estimated_impressions' => $impressions,
                'estimated_clicks'      => $clicks,

                'revenue_share'         => $rev_share,
                'currency'              => $this->currency,
                'amount'                => $amount,
                'amount_eur'            => $amount_eur,
                'amount_usd'            => $amount_usd,

                'created_at'            => $timestamp,
                'updated_at'            => $timestamp
            ];
        }
        return $records;
    }
}
