<?php

namespace App\Services\ARC\Sources\Providers\Zemanta;


use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

use Storage;

class ZemantaLibrary
{


    protected $client;
    protected $client_id;
    protected $client_secret;
    protected $tokenUrl;
    protected $baseUrl;

    public function __construct()
    {
        $this->tokenUrl = config('arc.sources.zemanta.token_api_url');
        $this->baseUrl = config('arc.sources.zemanta.base_api_url');
        $this->client_id = config('arc.sources.zemanta.client_id');
        $this->client_secret = config('arc.sources.zemanta.client_secret');


        $this->client = new Client;

    }

    public function requestPlacementReport($account_id, $start_date, $end_date)
    {
        $token = $this->getAccessToken(); // Getting Access Token (each time because it lasts 10 hours and the report is "daily")

        $path = 'reports/';

        $res = $this->client->post(
            $this->baseUrl . $path,
            [
                'headers' => [
                    'Authorization' => "{$token->token_type} {$token->access_token}",
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    "fields" => [
                        // Entity breakdown:
                        ["field" => "Account"],
                        ["field" => "Account Id"],
                        ["field" => "Campaign"],
                        ["field" => "Campaign Id"],
                        ["field" => "Ad Group"],
                        ["field" => "Ad Group Id"],
                        //["field" => "Content Ad"],
                        //["field" => "Content Ad Id"],

                        // Delivery breakdown:
                        ["field" => "Media Source"],
                        ["field" => "Media Source Slug"],
                        ["field" => "Media Source Id"],
                        ["field" => "Publisher"],
                        ["field" => "Placement"],
                        //["field" => "Environment"],
                        //["field" => "Device"],
                        //["field" => "Operating System"],
                        //["field" => "Country"],
                        //["field" => "State / Region"],
                        //["field" => "DMA"],

                        // Time breakdown:
                        ["field" => "Day"],
                        //["field" => "Week"],
                        //["field" => "Month"],

                        ["field" => "Impressions"],
                        ["field" => "Clicks"],
                        ["field" => "CTR"],
                        ["field" => "Avg. CPC"],
                        ["field" => "Avg. CPM"],
                        ["field" => "Yesterday Spend"],
                        ["field" => "Media Spend"],
                        ["field" => "Data Cost"],
                        ["field" => "License Fee"],
                        ["field" => "Total Spend"],
                        ["field" => "Margin"],
                        //["field" => "Total Spend + Margin"],
                        ["field" => "Visits"],
                        ["field" => "Unique Users"],
                        ["field" => "New Users"],
                        ["field" => "Returning Users"],
                        ["field" => "% New Users"],
                        ["field" => "Pageviews"],
                        ["field" => "Pageviews per Visit"],
                        ["field" => "Bounced Visits"],
                        ["field" => "Non-Bounced Visits"],
                        ["field" => "Bounce Rate"],
                        ["field" => "Total Seconds"],
                        ["field" => "Time on Site"],
                        ["field" => "Avg. Cost per Visit"],
                        ["field" => "Avg. Cost per New Visitor"],
                        ["field" => "Avg. Cost per Pageview"],
                        ["field" => "Avg. Cost per Non-Bounced Visit"],
                        ["field" => "Avg. Cost per Minute"],
                        ["field" => "Avg. Cost per Unique User"],
                        ["field" => "Account Status"],
                        ["field" => "Campaign Status"],
                        ["field" => "Ad Group Status"],
                        ["field" => "Content Ad Status"],
                        ["field" => "Media Source Status"],
                        ["field" => "Publisher Status"],
                        ["field" => "Video Start"],
                        ["field" => "Video First Quartile"],
                        ["field" => "Video Midpoint"],
                        ["field" => "Video Third Quartile"],
                        ["field" => "Video Complete"],
                        ["field" => "Video Progress 3s"],
                        ["field" => "Avg. CPV"],
                        ["field" => "Avg. CPCV"],
                        ["field" => "Measurable Impressions"],
                        ["field" => "Viewable Impressions"],
                        ["field" => "Not-Measurable Impressions"],
                        ["field" => "Not-Viewable Impressions"],
                        ["field" => "% Measurable Impressions"],
                        ["field" => "% Viewable Impressions"],
                        ["field" => "Impression Distribution (Viewable)"],
                        ["field" => "Impression Distribution (Not-Measurable)"],
                        ["field" => "Impression Distribution (Not-Viewable)"],
                        ["field" => "Avg. VCPM"],

                        ["field" => 'URL'],
                        ["field" => 'Display URL'],
                        ["field" => 'Brand Name'],
                        ["field" => 'Description'],
                        ["field" => 'Image Hash'],
                        ["field" => 'Image URL'],
                        ["field" => 'Call to action'],
                        ["field" => 'Label'],
                        ["field" => 'Uploaded'],
                        ["field" => 'Batch Name'],
                    ],
                    "options" => [
                        "showArchived" => true,
                    ],
                    "filters" => [
                        [ "field" => "Date",       "operator" => "between", "from" => $start_date, "to" => $end_date ],
                        [ "field" => "Account Id", "operator" => "=",       "value" => $account_id                   ],
                    ],
                ],
            ]
        );
        $job = json_decode((string)$res->getBody());
        
        //    Returns: {
        //      "data": {
        //        "id": "36378641",
        //        "status": "IN_PROGRESS",
        //        "result": null
        //      }
        //    }

        return $job->data->id;
    }

    public function getCampaigns($account_id)
    {
        $token = $this->getAccessToken(); // Getting Access Token (each time because it lasts 10 hours and the report is "daily")

        $path = 'campaigns/';

        $token = $this->getAccessToken(); // Getting Access Token (each time because it lasts 10 hours and the report is "daily")

        $marker = 0;
        $continue = true;
        $data = [];
        while($continue) {
            $res = $this->client->get(
                $this->baseUrl . $path,
                [
                    'headers' => [
                        'Authorization' => "{$token->token_type} {$token->access_token}",
                        'Content-Type' => 'application/json',
                    ],
                    'query' => [
                        'accountId' => $account_id,
                        'includeArchived' => 'true',
                        'onlyIds' => '',
                        'includeGoals' => '',
                        'includeBudgets' => 'true',
                        'marker' => $marker,
                        'limit' => 1
                    ]
                ]
            );
            $report = json_decode((string)$res->getBody());

            if(!empty($report->data)) {
                $data = array_merge($data, $report->data);
            }

            if(!empty($report->next)) {
                $ru = parse_url($report->next, PHP_URL_QUERY);
                $query = [];
                parse_str($ru, $query);
                $marker = $query['marker'];
                $continue = true;
            } else {
                $continue = false;
            }

            
        }
        

        return $data; 
    }

    public function requestReport($account_id, $start_date, $end_date, $conv_type = 'view')
    {
        $token = $this->getAccessToken(); // Getting Access Token (each time because it lasts 10 hours and the report is "daily")

        $path = 'reports/';


        $conv_field = 'conv - View attr.';
        if($conv_type == 'click') {
            $conv_field = 'conv - Click attr.';
        }

        $res = $this->client->post(
            $this->baseUrl . $path,
            [
                'headers' => [
                    'Authorization' => "{$token->token_type} {$token->access_token}",
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    "fields" => [
                        // Entity breakdown:
                        ["field" => "Account"],
                        ["field" => "Account Id"],
                        ["field" => "Campaign"],
                        ["field" => "Campaign Id"],
                        // ["field" => "Ad Group"],
                        // ["field" => "Ad Group Id"],
                        // ["field" => "Content Ad"],
                        // ["field" => "Content Ad Id"],

                        // Delivery breakdown:
                        //["field" => "Media Source"],
                        //["field" => "Media Source Slug"],
                        //["field" => "Media Source Id"],
                        //["field" => "Publisher"],
                        //["field" => "Placement"],
                        //["field" => "Environment"],
                        //["field" => "Device"],
                        //["field" => "Operating System"],
                        //["field" => "Country"],
                        //["field" => "State / Region"],
                        //["field" => "DMA"],

                        // Time breakdown:
                        ["field" => "Day"],
                        //["field" => "Week"],
                        //["field" => "Month"],

                        ["field" => "Impressions"],
                        ["field" => "Clicks"],
                        ["field" => "CTR"],
                        ["field" => "Avg. CPC"],
                        ["field" => "Avg. CPM"],
                        ["field" => "Yesterday Spend"],
                        ["field" => "Media Spend"],
                        ["field" => "Data Cost"],
                        ["field" => "License Fee"],
                        ["field" => "Total Spend"],
                        ["field" => "Margin"],
                        //["field" => "Total Spend + Margin"],
                        ["field" => "Visits"],
                        ["field" => "Unique Users"],
                        ["field" => "New Users"],
                        ["field" => "Returning Users"],
                        ["field" => "% New Users"],
                        ["field" => "Pageviews"],
                        ["field" => "Pageviews per Visit"],
                        ["field" => "Bounced Visits"],
                        ["field" => "Non-Bounced Visits"],
                        ["field" => "Bounce Rate"],
                        ["field" => "Total Seconds"],
                        ["field" => "Time on Site"],
                        ["field" => "Avg. Cost per Visit"],
                        ["field" => "Avg. Cost per New Visitor"],
                        ["field" => "Avg. Cost per Pageview"],
                        ["field" => "Avg. Cost per Non-Bounced Visit"],
                        ["field" => "Avg. Cost per Minute"],
                        ["field" => "Avg. Cost per Unique User"],
                        ["field" => "Account Status"],
                        ["field" => "Campaign Status"],
                        ["field" => "Ad Group Status"],
                        ["field" => "Content Ad Status"],
                        ["field" => "Media Source Status"],
                        ["field" => "Publisher Status"],
                        ["field" => "Video Start"],
                        ["field" => "Video First Quartile"],
                        ["field" => "Video Midpoint"],
                        ["field" => "Video Third Quartile"],
                        ["field" => "Video Complete"],
                        ["field" => "Video Progress 3s"],
                        ["field" => "Avg. CPV"],
                        ["field" => "Avg. CPCV"],
                        ["field" => "Measurable Impressions"],
                        ["field" => "Viewable Impressions"],
                        ["field" => "Not-Measurable Impressions"],
                        ["field" => "Not-Viewable Impressions"],
                        ["field" => "% Measurable Impressions"],
                        ["field" => "% Viewable Impressions"],
                        ["field" => "Impression Distribution (Viewable)"],
                        ["field" => "Impression Distribution (Not-Measurable)"],
                        ["field" => "Impression Distribution (Not-Viewable)"],
                        ["field" => "Avg. VCPM"],

                        //["field" => 'URL'],
                        //["field" => 'Display URL'],
                        //["field" => 'Brand Name'],
                        //["field" => 'Description'],
                        //["field" => 'Image Hash'],
                        //["field" => 'Image URL'],
                        //["field" => 'Call to action'],
                        //["field" => 'Label'],
                        //["field" => 'Uploaded'],
                        //["field" => 'Batch Name'],

                        
                        // ["field" => 'conv - View attr.'],
                        // ["field" => 'conv - Click attr.'],

                        ["field" => $conv_field],

                        
                    ],
                    "options" => [
                        "showArchived" => false,
                    ],
                    "filters" => [
                        [ "field" => "Date",       "operator" => "between", "from" => $start_date, "to" => $end_date ],
                        [ "field" => "Account Id", "operator" => "=",       "value" => $account_id                   ],
                    ],
                ],
            ]
        );
        $job = json_decode((string)$res->getBody());
        
        //    Returns: {
        //      "data": {
        //        "id": "36378641",
        //        "status": "IN_PROGRESS",
        //        "result": null
        //      }
        //    }

        return $job->data->id;
    }

    public function downloadReport($account_id, $job)
    {
        $token = $this->getAccessToken(); // Getting Access Token (each time because it lasts 10 hours and the report is "daily")

        $jobPath = 'reports/' . $job;

        $res = $this->client->get(
            $this->baseUrl . $jobPath,
            [
                'headers' => [
                    'Authorization' => "{$token->token_type} {$token->access_token}",
                    'Content-Type' => 'application/json',
                ],
            ]
        );
        $report = json_decode((string)$res->getBody());

        if($report->data->status == 'IN_PROGRESS') {
            return $report;
        }
        if(in_array($report->data->status, ['FAILED', 'CANCELLED'])) {
            Log::warning(json_encode($report));
            return $report;
        }
        if($report->data->status !== 'DONE') {
            return $report;
        }

        // report is done and available

        // Proceed
        $report->data->content = $this->client->get($report->data->result)->getBody()->__toString();
        
        return $report;
    }

    protected function getAccessToken()
    {
        $res = $this->client->post(
            $this->tokenUrl,
            [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Authorization' => 'Basic ' . base64_encode( $this->client_id . ':' . $this->client_secret ),
                ],
                'form_params' => [
                    'grant_type' => 'client_credentials',
                ],
            ]
        );
        $token = json_decode((string)$res->getBody());
    
        return $token;
    }
}
