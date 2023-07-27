<?php

namespace App\Services\ARC\Sources\Providers\ExploreAds;


use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;



class ExploreAdsLibrary
{
    protected $baseUrl;
    protected $client;

    protected $validReportTypes = ['rs1', 'rs2', 'conversions', 'trademark'];

    protected $errors = [];

    public function __construct()
    {

        $this->client = new Client([
            'timeout'  => 60.0,
            'connect_timeout' => 15,
            'verify' => false
        ]);
    }

    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param $report_type: rs1 (google), rs2 (yahoo), conversions, trademark 
     * 
     * 
     */
    public function getReport($report_type, $start_date, $end_date)
    {
        $this->baseUrl = config('arc.sources.exploreads.api_url');

        if (!in_array($report_type, $this->validReportTypes)) {
            $this->errors[] = 'Invalid report_type : ' . $report_type;
            return false;
        }

        $body = [
            'email'         => config('arc.sources.exploreads.credentials.username'),
            'password'      => config('arc.sources.exploreads.credentials.password'),
            'startDate'     => $start_date,
            'endDate'       => $end_date,
            'reportType'    => $report_type
        ];

        try {
            $res = $this->client->post($this->baseUrl, ['json' => $body, 'debug' => false]);
            $res = json_decode($res->getBody());
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $r = $e->getResponse();
            $res = json_decode($r->getBody());
            Log::warning("[ExploreAdsLibrary] " . json_encode($res));
            $this->errors[] = $res;
            return false;
        } catch (\Exception $e) {
            Log::warning("[ExploreAdsLibrary] {$e->getMessage()}");
            $this->errors[] = "[ExploreAdsLibrary] {$e->getMessage()}";
            return false;
        }

        return $res;
    }

    public function getHourlyReport($report_type, $start_date, $end_date)
    {
        $this->baseUrl = config('arc.sources.exploreads.api_url_hourly');

        $body = [
            'email'         => config('arc.sources.exploreads.credentials.username'),
            'password'      => config('arc.sources.exploreads.credentials.password'),
            'startDate'     => $start_date,
            'endDate'       => $end_date,
            'hour'          => 'hour of the day 0-23'
        ];

        try {
            $res = $this->client->post($this->baseUrl, ['json' => $body, 'debug' => false]);
            $res = json_decode($res->getBody());
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $r = $e->getResponse();
            $res = json_decode($r->getBody());
            Log::warning("[ExploreAdsLibrary] " . json_encode($res));
            $this->errors[] = $res;
            return false;
        } catch (\Exception $e) {
            Log::warning("[ExploreAdsLibrary] {$e->getMessage()}");
            $this->errors[] = "[ExploreAdsLibrary] {$e->getMessage()}";
            return false;
        }

        return $res;
    }
}