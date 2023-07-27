<?php

namespace App\Services\ARC\Sources\Providers\System1;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class System1Library
{
    protected $api_key;
    protected $baseUrl = 'https://reports.system1.com/v3/';
    protected $client;


    public function __construct($api_key)
    {
        $this->api_key = $api_key;
        $this->client = new Client(['verify' => false]);
    }


    public function isDataAvailable($date)
    {
        $request_uri = 'status';
        $params = [
            'auth_key'  => $this->api_key,
            'days'      => $date
        ];

        try {
            // Request report download
            $url = $this->baseUrl . $request_uri . '?' . http_build_query($params);
            $response = $this->client->get($url, ['debug' => false]);
            $response = json_decode($response->getBody());
            return !empty($response);
        } 
        catch (\Exception $e) {
            Log::warning('[System1AfsLibrary][getDataStatus] Unable to Identify The Data Status for `'.$date.'`: ' . $e->getMessage());
            
            if(stripos($e->getMessage(), '401 unauthorized')) {
                throw $e;
            }
            return false;
        }
    }

    public function downloadSubIdReport($date = null)
    {
        $request_uri = 'subid_daily';
        $params = [
            'auth_key' => $this->api_key,
            'days' => $date
        ];


        try {
            // Request report download
            $url = $this->baseUrl . $request_uri . '?' . http_build_query($params);
            $response = $this->client->get($url, ['debug' => false]);
            $response = json_decode($response->getBody());

            $data = [];
            if(!empty($response)) {
                $header = array_shift($response);
                foreach($response as $el) {
                    $tmp = [];
                    foreach($el as $field_idx => $value) {
                        $tmp[ $header[$field_idx] ] = $value;
                    }
                    $data[] = $tmp;
                }
            }

            return [ 'status' => true, 'data' => $data ];
        } 
        catch (\Exception $e) {
            Log::warning('[System1Library][downloadSubIdReport] Download Error Daily SubId Report: ' . $e->getMessage());
            return [ 
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }

}