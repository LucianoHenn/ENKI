<?php

namespace App\Services\ARC\Sources\Providers\BingAdsRevenue;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Carbon;

class BingAdsRevenueLibrary
{
    protected $api_base_url;
    protected $api_key;

    protected $client;

    public function __construct($api_base_url = '', $api_key = '')
    {
        if(!empty($api_base_url)) {
            $this->api_base_url = $api_base_url;
        } else {
            $this->api_base_url = config('arc.sources.bingadsrevenue.api_base_url');
        }

        if(!empty($api_key)) {
            $this->api_key = $api_key;
        } else {
            $this->api_key = config('arc.sources.bingadsrevenue.api_key');
        }

        $this->client = new Client(['verify' => false]);
    }

    public function downloadData($date)
    {

        try {
            // Request report download
            $params = [
                'an' => $this->api_key,
                'reportType' => 'TypeTag',
                'startDate' => Carbon::parse($date)->subDays(7)->format('Y-m-d'),
                'endDate' => $date,
                'granularity' => 'Day',
                'includeTypeTag' =>false
            ];
            $url = $this->api_base_url;
            
            $response = $this->client->get(
                $url, 
                [
                    'query' => $params,
                    'debug' => false,
                    'timeout' => 300, // Response timeout
                    'connect_timeout' => 60, // Connection timeout
            ]);
            
            $response = (string)$response->getBody();
            return $response;
        } 
        catch (\Exception $e) {
            $msg = 'Unable to Download The Data for `'.$date.'`: ' . $e->getMessage();
            Log::warning('[BingAdsRevenueLibrary][downloadData] ' . $msg);
            return [ 'status' => false, 'error' => $msg];
        }
    }
}