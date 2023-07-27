<?php

namespace App\Services\ARC\Sources\Providers\ExploreAds\Daily;

use App\Services\ARC\Sources\Abstracts\BaseImporter;
use App\Models\CurrencyConversion;
use App\Models\GoogleGeoTarget;
use App\Models\ARC\ReportLogbook;
use App\Models\ARC\Providers\ExploreAds\ExploreAdsDailyReportData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Url\Url;

use League\Csv\Statement;
use League\Csv\Reader;

use Exception;
use Illuminate\Support\Carbon as Carbon;
use Illuminate\Support\Facades\Storage as Storage;
use App\Models\ClientArcAssociation;

use App\Models\Bbtrk\ExploreAds\TrackOut;
use App\Services\ARC\Sources\Providers\GoogleAds\GoogleAdsLibrary;
use App\Services\ARC\Sources\Providers\Taboola\TaboolaLibrary;
use App\Services\ARC\Sources\Providers\Facebook\FacebookLibrary;

/**
 * Class ExploreAdsDailyImporter
 */
class ExploreAdsDailyImporter extends BaseImporter
{

    public $send_created_event = false;
    protected $revenue_share = 1.0;
    protected $currency = 'EUR';

    protected $campaigns = [];

    protected $taboola_account_mapping = [
        'bns' => 'bidberrysrl-newbiz-sc',
        'bns2' => 'bidberrysrl-newexplorads-sc',
    ];

    protected $country_mappings = [
        'Türkiye' => 'Turkey'
    ];

    public function doImport(ReportLogbook $request)
    {
        $this->campaigns = [];
        $identifier = $request->identifier;
        $table = $this->getReportTableName($request);

        $validAssociations = ClientArcAssociation::where('source', $this->source)
            ->inPeriod($request->date_end)
            ->get();

        list($ad_client_id, $channels_prefix) = explode('__', $identifier);
        $activeAssoc = null;
        foreach ($validAssociations as $assoc) {

            if ($assoc->info['ad_client_id'] == $ad_client_id && $assoc->info['channels_prefix'] == $channels_prefix) {
                $activeAssoc = $assoc;
                break;
            }
        }

        Log::info("[ExploreAdsDailyImporter] Start importing for identifier {$identifier}");

        try {
            $count = 0;
            // Read the CSV
            $localReport = $request->infoOriginalLocalReport;

            $data = json_decode(Storage::disk('system')->get($localReport), true);


            if (empty($data)) { // Note: depends from source
                Log::info("[ExploreAdsDailyImporter] Empty data on {$localReport}");
                return 0;
            }

            // Clear data BEFORE importing new data
            $this->deleteData($request);


            $this->getCampaigns($request);


            collect($data)
                ->chunk($this->insert_chunk_size)
                ->each(function ($chunkDataRows) use ($request, $table, $activeAssoc, $channels_prefix) {
                    $toInsert = [];
                    foreach ($chunkDataRows as $dataRow) {
                        $el = $this->processRecord($dataRow, $request, $activeAssoc, $channels_prefix);

                        if (!empty($el)) {
                            $toInsert[] = $el;
                        }
                    }
                    if (!empty($toInsert)) {


                        $this->insert($table, $toInsert);
                    }
                });

            return count($data);
        } catch (Exception $e) {
            // In case of exception, set update failed and rollBack DB
            throw $e;
        }
    }

    protected function getChannelAssociation($channel, $device, $country)
    {
        $dev = 'dsk';
        if (in_array($device, ['Tablet', 'HighEndMobile'])) {
            $dev = 'mob';
        }
        $countryCode = 'US';

        if (isset($this->country_mappings[$country])) {
            $country = $this->country_mappings[$country];
        }
        $c = GoogleGeoTarget::where('target_type', 'Country')
            ->where('canonical_name', $country)->limit(1)->first();

        if (!is_null($c)) {
            $countryCode = strtoupper($c->country_code);
        }

        $x = $this->campaigns[$dev][$countryCode][$channel] ?? null;

        if (!is_null($x)) return $x;


        //try too find whether we have the same channel, same country, opposite device.
        $dev2 = 'mob';
        if ($dev == 'mob') {
            $dev2 = 'dsk';
        }
        $y = $this->campaigns[$dev2][$countryCode][$channel] ?? null;

        if (!is_null($y)) return $y;

        //try to look the campaign for the same device
        foreach ($this->campaigns[$dev] as $ctC => $channels) {
            if (isset($channels[$channel])) {
                return $channels[$channel];
            }
        }

        //try to look the campaign for the opposite device
        foreach ($this->campaigns[$dev2] as $ctC => $channels) {
            if (isset($channels[$channel])) {
                return $channels[$channel];
            }
        }

        return null;
    }

    protected function getCampaigns($request)
    {
        $gLib = new GoogleAdsLibrary();
        $dt = Carbon::parse($request->date_end)->subDays(2);
        $res = TrackOut::select('acid', 'campaign_id', 'to_asid')
            ->where('date', '>=', $dt->format('Y-m-d'))
            ->groupByRaw('acid, campaign_id, to_asid')
            ->get();

        $google_accounts = [];
        $taboola_accounts = [];
        $facebook_accounts = [];
        $cmpAsidMapping = [];
        foreach ($res as $e) {
            if (!is_numeric($e->acid)) {
                $match = [];
                if (isset($this->taboola_account_mapping[$e->acid])) {
                    $taboola_accounts[] = $this->taboola_account_mapping[$e->acid];
                } elseif (preg_match('/f\d+/', $e->acid, $match)) {
                    $facebook_accounts[] = str_replace('f', '', $e->acid);
                }

                continue;
            }
            $google_accounts[] = $e->acid;
            //$cmpAsidMapping[$e->campaign_id] = $e->to_asid;
        }

        $google_accounts    = array_merge(array_unique($google_accounts));

        $taboola_accounts   = array_merge(array_unique($taboola_accounts));
        $facebook_accounts  = array_merge(array_unique($facebook_accounts));

        //Retrieving google data

        //dd($account_ids);
        $this->campaigns = [];
        foreach ($google_accounts as $acid) {
            $cmpAsidMapping = [];
            $ads = $gLib->getCustomerIdAds($acid);
            foreach ($ads as $ad) {
                $cmId = $ad->campaign->id;
                $finalUrl = $ad->adGroupAd->ad->finalUrls[0];
                $u = Url::fromString($finalUrl);
                $cmpAsidMapping[$cmId] = $u->getQueryParameter('asid');
            }
            if ($gLib->getCustomerIdCampaigns($acid)) {
                foreach ($gLib->getData() as $el) {
                    if ($el->campaign->status == 'REMOVED') continue;
                    //if ($el->campaign->status == 'PAUSED') continue;
                    $cmpName = strtolower($el->campaign->name);
                    $cmChunks = explode('_', $cmpName);
                    $device = 'dsk';
                    $countryCode = strtoupper($cmChunks[1]);
                    if ($countryCode == 'UK') {
                        $countryCode = 'GB';
                    }
                    $isSec = false;
                    foreach ($cmChunks as $chunk) {
                        if ($chunk == 'sec') {
                            $isSec = true;
                            break;
                        }
                        if (in_array(strtolower($chunk), ['dsk', 'mob', 'mobile'])) {
                            $device = strtolower($chunk);
                            if ($device == 'mobile') $device = 'mob';
                        }
                    }

                    if (!$isSec) {
                        $to_asid = $cmpAsidMapping[$el->campaign->id] ?? 'unk';
                        if (!isset($this->campaigns[$device][$countryCode][$to_asid])) {
                            $this->campaigns[$device][$countryCode][$to_asid] = [
                                'account_id' => $acid,
                                'campaign_id' => $el->campaign->id,
                                'campaign_name' => $el->campaign->name,
                                'campaign_status' => $el->campaign->status
                            ];
                        } elseif ($this->campaigns[$device][$countryCode][$to_asid]['campaign_status'] != $el->campaign->status && $el->campaign->status == 'ENABLED') {
                            $this->campaigns[$device][$countryCode][$to_asid] = [
                                'account_id' => $acid,
                                'campaign_id' => $el->campaign->id,
                                'campaign_name' => $el->campaign->name,
                                'campaign_status' => $el->campaign->status
                            ];
                        }
                    }
                }
            }
        }



        if (!empty($taboola_accounts)) {
            $tbl = new TaboolaLibrary(config('arc.sources.taboola.client_id'), config('arc.sources.taboola.client_secret'));

            foreach ($taboola_accounts as $tbAct) {
                $tbl->setAccountId($tbAct);
                $cmps = $tbl->getAllCampaigns();
                if (!empty($cmps->results)) {
                    foreach ($cmps->results as $cmp) {

                        if ($cmp->is_active && $cmp->status == 'RUNNING') {
                            if (stripos($cmp->name, 'SHOE') === FALSE) continue;

                            $cmpName = strtolower($cmp->name);
                            $cmChunks = explode('_', $cmpName);
                            $to_asid = $cmChunks[count($cmChunks) - 1];
                            if (!is_numeric($to_asid)) {
                                $to_asid  = 'unk';
                            } else {
                                $to_asid = 'zmw_ch' . $to_asid;
                            }
                            $device = 'dsk';
                            $countryCode = strtoupper($cmChunks[1]);
                            if ($countryCode == 'UK') {
                                $countryCode = 'GB';
                            }
                            foreach ($cmChunks as $chunk) {
                                if (in_array(strtolower($chunk), ['dsk', 'mob', 'mobile'])) {
                                    $device = strtolower($chunk);
                                    if ($device == 'mobile') $device = 'mob';
                                    break;
                                }
                            }
                            //$to_asid = $cmpAsidMapping[$el->campaign->id] ?? 'unk';
                            $this->campaigns[$device][$countryCode][$to_asid] = [
                                'account_id' => $tbAct,
                                'campaign_id' => $cmp->id,
                                'campaign_name' => $cmp->name
                            ];
                        }
                    }
                }
            }
        }

        
        if (!empty($facebook_accounts)) {
            $fbl = new FacebookLibrary();
            foreach ($facebook_accounts as $fbAct) {
                $cmps = $fbl->getAccountCampaigns($fbAct);

                if (!empty($cmps)) {
                    foreach ($cmps as $cmp) {

                        if ($cmp->effective_status == 'ACTIVE') {
                            //if (stripos($cmp->name, 'SHOE') === FALSE) continue;

                            $cmpName = strtolower($cmp->name);
                            $cmChunks = explode('_', $cmpName);
                            $to_asid = $cmChunks[count($cmChunks) - 1];
                            if (!is_numeric($to_asid)) {
                                $to_asid  = 'unk';
                            } else {
                                $to_asid = 'zmw_ch' . $to_asid;
                            }
                            $device = 'dsk';
                            $countryCode = strtoupper($cmChunks[1]);
                            if ($countryCode == 'UK') {
                                $countryCode = 'GB';
                            }
                            foreach ($cmChunks as $chunk) {
                                if (in_array(strtolower($chunk), ['dsk', 'mob', 'mobile'])) {
                                    $device = strtolower($chunk);
                                    if ($device == 'mobile') $device = 'mob';
                                    break;
                                }
                            }
                            //$to_asid = $cmpAsidMapping[$el->campaign->id] ?? 'unk';
                            $this->campaigns[$device][$countryCode][$to_asid] = [
                                'account_id' => 'act_' . $fbAct,
                                'campaign_id' => $cmp->id,
                                'campaign_name' => $cmp->name
                            ];
                        }
                    }
                }
            }
        }
    }

    protected function processRecord($dataRow, $request, $activeAssoc, $channels_prefix)
    {


        if(stripos($dataRow['custom_channel_name'], $channels_prefix) === FALSE) return null;



        $amount = $dataRow['earnings_eur'] ?? 0;
        $amount_eur = $amount_usd = 0;

        if ($this->currency === "EUR") {
            $amount_eur = $amount;
            $amount_usd = CurrencyConversion::convertAmount($dataRow['date'], $this->currency, 'USD', $amount, 4, true);
        } else { //$currency === "USD") {
            $amount_usd = $amount;
            $amount_eur = CurrencyConversion::convertAmount($dataRow['date'], $this->currency, 'EUR', $amount, 4, true);
        }

        $caRow = $this->getChannelAssociation($dataRow['custom_channel_name'], $dataRow['platform_type_code'], trim(str_replace('"', '', $dataRow['country_name'])));

        $domain = '';

        $record = [
            'date'                      => $dataRow['date'],
            'identifier'                => $request->identifier,

            'client_id'                 => $activeAssoc->client_id,
            'market_id'                 => $activeAssoc->market_id,
            'market'                    => $activeAssoc->market->code,

            'ad_client_id'              => $dataRow['ad_client_id'],
            'device'                    => $dataRow['platform_type_code'],
            'country'                   => trim(str_replace('"', '', $dataRow['country_name'])),
            'custom_channel_name'       => $dataRow['custom_channel_name'],

            'domain'                    => $domain,
            'account_id'                => $caRow['account_id'] ?? '',
            'campaign_id'               => $caRow['campaign_id'] ?? '',

            'hash'                      => ExploreAdsDailyReportData::getHash([
                $dataRow['ad_client_id'],
                $dataRow['platform_type_code'],
                $dataRow['country_name'],
                $dataRow['custom_channel_name']
            ]),
            'ad_requests'               => $dataRow['ad_requests'],
            'page_views'                => $dataRow['page_views'],
            'clicks'                    => $dataRow['clicks'],
            'individual_ad_impressions' => $dataRow['individual_ad_impressions'],
            'matched_ad_requests'       => $dataRow['matched_ad_requests'],
            'clicks_spam_ratio'         => floatVal(str_replace('%', '', ($dataRow['clicks_spam_ratio'] ?? '0')) / 100),
            'rpc'                       => $dataRow['rpc'],

            'revenue_share'             => $this->revenue_share,
            'currency'                  => $this->currency,
            'amount'                    => $amount,
            'amount_eur'                => $amount_eur,
            'amount_usd'                => $amount_usd,
            'created_at'                => now(),
            'updated_at'                => now()
        ];


        return $record;
    }


    public function getChannelAssociationOLD($date, $channel)
    {
        $caRow = TrackOut::where('to_asid', $channel)
            ->where('date', $date)
            ->limit(1)
            ->first();
        return $caRow;
    }
}
