<?php

namespace App\Services\ARC\Sources\Providers\TikTok;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Storage;
use GuzzleHttp\Client;

class TikTokLibrary
{

    protected $client;
    protected $app_id;
    protected $app_secret;
    protected $base_api_url;
    protected $access_token;
    protected $error;

    public function __construct()
    {
        $this->app_id               = config('arc.sources.tiktok.app_id');
        $this->app_secret           = config('arc.sources.tiktok.app_secret');
        $this->base_api_url         = config('arc.sources.tiktok.base_api_url');

        $this->client = new Client([
            'base_uri' => $this->base_api_url,
            'verify' => false,
            'headers' => ['Content-Type' => 'application/json']
        ]);
    }

    public function setAccessToken($access_token)
    {
        $this->access_token = $access_token;
    }

    public function getAccessToken($auth_code)
    {
        $api_path = 'oauth2/access_token/';
        $query = [
            'app_id' => $this->app_id,
            'secret' => $this->app_secret,
            'auth_code' => $auth_code
        ];

        try {


            $res = $this->client->post($api_path, ['json' => $query, 'debug' => false]);
            $response = json_decode($res->getBody());

            return(object) [ 
                'http_code' => $res->getStatusCode(),
                'response' => $response
            ];
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $r = $e->getResponse();
            $response = json_decode($r->getBody());
            Log::warning("[TikTokLibrary] " . $res->error->message ?? $e->getMessage());
            

            return false;
        } catch (\Exception $e) {
            Log::warning("[TikTokLibrary] {$e->getMessage()}");
            $this->error = "[TikTokLibrary] {$e->getMessage()}";

            return false;
        }
    }

    public function getAdGroups($advertiser_id)
    {
        $api_path = 'adgroup/get/';

        try {


            $res = $this->client->get(
                $api_path, [
                    'headers' => [
                        'Access-Token' => $this->access_token
                    ], 
                    'json' => [
                        'advertiser_id' => $advertiser_id,
                        'page_size' => 1000
                    ], 'debug' => false
                ]);
            $response = json_decode($res->getBody());

            return(object) [ 
                'http_code' => $res->getStatusCode(),
                'response' => $response
            ];
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $r = $e->getResponse();
            
            $response = json_decode($r->getBody());
            Log::warning("[TikTokLibrary][getAdGroups] " . $e->getMessage());
            

            return false;
        } catch (\Exception $e) {
            Log::warning("[TikTokLibrary][getAdGroups] {$e->getMessage()}");
            $this->error = "[TikTokLibrary][getAdGroups] {$e->getMessage()}";

            return false;
        }
    }

    public function getCampaigns($advertiser_id)
    {
        $api_path = 'campaign/get/';

        try {


            $res = $this->client->get(
                $api_path, [
                    'headers' => [
                        'Access-Token' => $this->access_token
                    ], 
                    'json' => [
                        'advertiser_id' => $advertiser_id,
                        'page_size' => 1000
                    ], 'debug' => false
                ]);
            $response = json_decode($res->getBody());

            return(object) [ 
                'http_code' => $res->getStatusCode(),
                'response' => $response
            ];
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $r = $e->getResponse();
            
            $response = json_decode($r->getBody());
            Log::warning("[TikTokLibrary][getCampaigns] " . $e->getMessage());
            

            return false;
        } catch (\Exception $e) {
            Log::warning("[TikTokLibrary][getCampaigns] {$e->getMessage()}");
            $this->error = "[TikTokLibrary][getCampaigns] {$e->getMessage()}";

            return false;
        }
    }

    public function getSyncReport($advertiser_id, $date_begin, $date_end)
    {
        $api_path = 'report/integrated/get/';

        try {


            $res = $this->client->get(
                $api_path, [
                    'headers' => [
                        'Access-Token' => $this->access_token
                    ], 
                    'json' => [
                        'advertiser_id' => $advertiser_id,
                        'start_date' => $date_begin,
                        'end_date' => $date_end,
                        'page_size' => 1000,
                        'report_type' => 'BASIC',
                        'data_level' => 'AUCTION_ADGROUP',
                        'dimensions'=> ['adgroup_id', 'stat_time_day'],
                        'metrics' => [ 
                            'campaign_name', 'objective_type', 'campaign_id',
                            'adgroup_name', 'spend', 'impressions',
                            'clicks', 'conversion', 'cost_per_conversion',
                            'real_time_conversion', 'real_time_cost_per_conversion', 'currency'
                        ]
                    ], 'debug' => false
                ]);
            $response = json_decode($res->getBody());

            return(object) [ 
                'http_code' => $res->getStatusCode(),
                'response' => $response
            ];
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $r = $e->getResponse();
            
            $response = json_decode($r->getBody());
            Log::warning("[TikTokLibrary][getSyncReport] " . $e->getMessage());
            

            return false;
        } catch (\Exception $e) {
            Log::warning("[TikTokLibrary][getSyncReport] {$e->getMessage()}");
            $this->error = "[TikTokLibrary][getSyncReport] {$e->getMessage()}";

            return false;
        }
    }

    public function getAdvertiserAccountsList()
    {
        $api_path = 'oauth2/advertiser/get/';

        try {


            $res = $this->client->get(
                $api_path, [
                    'headers' => [
                        'Access-Token' => $this->access_token
                    ], 
                    'query' => [
                        'app_id' => $this->app_id,
                        'secret' => $this->app_secret,
                    ], 'debug' => false
                ]);
            $response = json_decode($res->getBody());

            return(object) [ 
                'http_code' => $res->getStatusCode(),
                'response' => $response
            ];
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $r = $e->getResponse();
            
            $response = json_decode($r->getBody());
            Log::warning("[TikTokLibrary] " . $e->getMessage());
            

            return false;
        } catch (\Exception $e) {
            Log::warning("[TikTokLibrary] {$e->getMessage()}");
            $this->error = "[TikTokLibrary] {$e->getMessage()}";

            return false;
        }
    }


    public function requestReport($advertiser_id, $start_date, $end_date)
    {
        $api_path = 'reports/integrated/get/';
        $params = [
            'advertiser_id' => $advertiser_id,
            'dimensions' => [ 'ad_id', 'stat_time_day' ],
            'metrics' => [
                'campaign_name',
                'objective_type',
                'campaign_id',
                'adgroup_name',
                'placement',
                'adgroup_id',
                'ad_name',
                'ad_text',

                'spend',
                'cpc',
                'cpm',
                'impressions',
                'clicks',
                'reach',
                'conversion',
                'cost_per_conversion',
                'result',
                'cost_per_result',
                'frequency',
                'currency',
            ],
            'start_date' => $start_date,
            'end_date' => $end_date,
        ];
        try {

            $res = $this->client->get(
                $api_path, [
                    'headers' => ['Access-Token' => $this->access_token], 
                    'query' => $params, 'debug' => false
                ]);
            $response = json_decode($res->getBody());

            return(object) [ 
                'http_code' => $res->getStatusCode(),
                'response' => $response
            ];
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $r = $e->getResponse();
            $response = json_decode($r->getBody());
            Log::warning("[TikTokLibrary] " . $res->error->message ?? $e->getMessage());
            

            return false;
        } catch (\Exception $e) {
            Log::warning("[TikTokLibrary] {$e->getMessage()}");
            $this->error = "[TikTokLibrary] {$e->getMessage()}";

            return false;
        }
    }
}
