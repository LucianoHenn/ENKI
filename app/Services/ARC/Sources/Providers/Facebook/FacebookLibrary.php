<?php

namespace App\Services\ARC\Sources\Providers\Facebook;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Exception;


class FacebookLibrary
{

    protected $access_token = '';
    protected $business_id  = '';
    protected $base_api_url = '';


    protected $error;

    public function __construct()
    {
        $this->access_token = config('arc.sources.facebook.access_token');
        $this->business_id = config('arc.sources.facebook.business_id');
        $this->base_api_url = config('arc.sources.facebook.base_api_url');
    }


    public function getLastError()
    {
        return $this->error;
    }

    protected function getAccessToken()
    {
        return $this->access_token;
    }

    public function getAccountAdsets($account_id)
    {
        $client = new Client(['verify' => false]);
        // Get a client so we can work with FB API
        $fbToken = $this->getAccessToken();
        if (empty($fbToken)) {
            $this->error = (object) [
                'status' => false,
                'error' => 'Access Token not found',
                'message' => 'Access Token not found'
            ];
            Log::warning("[FacebookLibrary] {$this->error->message}");
            return false;
        }

        $this->error = '';

        $fields = [
            'account_id',
            'campaign_id',
            'id',
            'name',
            'created_time',
            'start_time',
            'end_time',
            'status',
            'effective_status',
            'bid_strategy',
            'bid_amount',
            'bid_adjustments',
            'bid_constraints',
            'daily_budget',
            'daily_min_spend_target',
            'daily_spend_cap',
            'lifetime_budget'
        ];

        $params = [
            'access_token' => $fbToken,
            'fields' => json_encode($fields),
            'limit' => 500
        ];

        $url = $this->base_api_url . '/act_' . $account_id . '/adsets?' . http_build_query($params);
        $data = [];

        try {
            do {
                $resp = $client->get($url);
                $resp = json_decode($resp->getBody());
                if ($resp === false) {
                    if (empty($data)) {
                        return false;
                    }
                    break;
                }
                $data = array_merge($data, $resp->data);

                if (!empty($resp->paging->cursors->after)) {
                    $params['after'] = $resp->paging->cursors->after;
                    $url = $this->base_api_url . '/act_' . $account_id . '/adsets?' . http_build_query($params);
                } else {
                    $url = '';
                }
            } while (!empty($url));
            return $data;
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            Log::warning(json_encode([
                'log' => '[ARC][FacebookLibrary][getAccountAdsets]',
                'log_type' => 'warning',
                'status' => false,
                'exception' => '\GuzzleHttp\Exception\ClientException',
                'message' => $e->getResponse()->getReasonPhrase()
            ]));
            $this->error = (object) [
                'status' => false,
                'error' => '\GuzzleHttp\Exception\ClientException',
                'message' => $e->getResponse()->getReasonPhrase()
            ];
        } catch (\Exception $e) {
            Log::warning(json_encode([
                'log' => '[ARC][FacebookLibrary][getAccountAdsets]',
                'log_type' => 'warning',
                'status' => false,
                'exception' => get_class($e),
                'message' => $e->getMessage()
            ]));
            $this->error = [
                'status' => false,
                'error' => get_class($e),
                'message' => $e->getMessage()
            ];
        }
    }

    public function getAccountCreatives($account_id)
    {
        $client = new Client(['verify' => false]);
        // Get a client so we can work with FB API
        $fbToken = $this->getAccessToken();
        if (empty($fbToken)) {
            $this->error = (object) [
                'status' => false,
                'error' => 'Access Token not found',
                'message' => 'Access Token not found'
            ];
            Log::warning("[FacebookLibrary] {$this->error->message}");
            return false;
        }

        $this->error = '';

        $fields = [
            'account_id', 'adset_id', 'campaign_id', 'effective_status',
            'adcreatives{actor_id,object_story_spec,asset_feed_spec,link_url,object_url}'
        ];

        $params = [
            'access_token' => $fbToken,
            'fields' => json_encode($fields),
            'limit' => 150
        ];

        $url = $this->base_api_url . '/act_' . $account_id . '/ads?' . http_build_query($params);
        $data = [];

        try {
            do {
                $resp = $client->get($url);
                $resp = json_decode($resp->getBody());
                if ($resp === false) {
                    if (empty($data)) {
                        return false;
                    }
                    break;
                }
                $data = array_merge($data, $resp->data);

                if (!empty($resp->paging->cursors->after)) {
                    $params['after'] = $resp->paging->cursors->after;
                    $url = $this->base_api_url . '/act_' . $account_id . '/ads?' . http_build_query($params);
                } else {
                    $url = '';
                }
            } while (!empty($url));
            return $data;
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            Log::warning(json_encode([
                'log' => '[ARC][FacebookLibrary][getAccountCreatives]',
                'log_type' => 'warning',
                'status' => false,
                'exception' => '\GuzzleHttp\Exception\ClientException',
                'message' => $e->getResponse()->getReasonPhrase()
            ]));
            $this->error = (object) [
                'status' => false,
                'error' => '\GuzzleHttp\Exception\ClientException',
                'message' => $e->getResponse()->getReasonPhrase()
            ];
        } catch (\Exception $e) {
            Log::warning(json_encode([
                'log' => '[ARC][FacebookLibrary][getAccountCreatives]',
                'log_type' => 'warning',
                'status' => false,
                'exception' => get_class($e),
                'message' => $e->getMessage()
            ]));
            $this->error = [
                'status' => false,
                'error' => get_class($e),
                'message' => $e->getMessage()
            ];
        }
    }

    public function getAccountCampaigns($account_id)
    {
        $client = new Client(['verify' => false]);
        // Get a client so we can work with FB API
        $fbToken = $this->getAccessToken();
        if (empty($fbToken)) {
            $this->error = (object) [
                'status' => false,
                'error' => 'Access Token not found',
                'message' => 'Access Token not found'
            ];
            Log::warning("[FacebookLibrary] {$this->error->message}");
            return false;
        }

        $this->error = '';

        $fields = [
            'account_id',
            'adlabels',
            'effective_status',
            'created_time',
            'daily_budget',
            'lifetime_budget',
            'objective',
            'updated_time',
            'start_time',
            'stop_time',
            'status',
            'special_ad_categories',
            'name'
        ];

        $params = [
            'access_token' => $fbToken,
            'fields' => json_encode($fields),
            'limit' => 500
        ];

        $url = $this->base_api_url . '/act_' . $account_id . '/campaigns?' . http_build_query($params);
        $data = [];

        try {
            do {
                $resp = $client->get($url);
                $resp = json_decode($resp->getBody());
                if ($resp === false) {
                    if (empty($data)) {
                        return false;
                    }
                    break;
                }
                $data = array_merge($data, $resp->data);

                if (!empty($resp->paging->cursors->after)) {
                    $params['after'] = $resp->paging->cursors->after;
                    $url = $this->base_api_url . '/act_' . $account_id . '/campaigns?' . http_build_query($params);
                } else {
                    $url = '';
                }
            } while (!empty($url));
            return $data;
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            Log::warning(json_encode([
                'log' => '[ARC][FacebookLibrary][getAccountCampaigns]',
                'log_type' => 'warning',
                'status' => false,
                'exception' => '\GuzzleHttp\Exception\ClientException',
                'message' => $e->getResponse()->getReasonPhrase()
            ]));
            $this->error = (object) [
                'status' => false,
                'error' => '\GuzzleHttp\Exception\ClientException',
                'message' => $e->getResponse()->getReasonPhrase()
            ];
        } catch (\Exception $e) {
            Log::warning(json_encode([
                'log' => '[ARC][FacebookLibrary][getAccountCampaigns]',
                'log_type' => 'warning',
                'status' => false,
                'exception' => get_class($e),
                'message' => $e->getMessage()
            ]));
            $this->error = [
                'status' => false,
                'error' => get_class($e),
                'message' => $e->getMessage()
            ];
        }
    }

    public function downloadReport(string $account_id, string $jobId)
    {
        $client = new Client(['verify' => false]);
        // Get a client so we can work with FB API
        $fbToken = $this->getAccessToken();
        if (empty($fbToken)) {
            $this->error = (object) [
                'status' => false,
                'error' => 'Access Token not found',
                'message' => 'Access Token not found'
            ];
            Log::warning("[FacebookLibrary] {$this->error->message}");
            return false;
        }

        $data = ['access_token' => $fbToken];

        // Request report download
        $url = $this->base_api_url . '/' . $jobId . '?' . http_build_query($data);

        try {
            $response = $client->get($url, ['debug' => false]);
            $status_desc = json_decode($response->getBody());
        } catch (\Exception $e) {
            Log::warning('[FacebookLibrary][downloadReport] Exception: ' . $e->getMessage());
            $status_desc = false;
        }

        // Check request Status
        if ($status_desc === false) {
            $this->error  = "Request failed";
            Log::warning("[FacebookLibrary][downloadReport] {$this->error}");
            return false;
        }

        $status = strtolower($status_desc->async_status);
        // Check status from the request
        if ($status == 'job completed') { // JOB COMPLETED
            Log::info('[FacebookLibrary][' . $jobId . '] Report job completed');

            /* Download file */
            $res = $this->downloadFile($account_id, $jobId);

            /*-- If it's an exception, go to next account --*/
            if (empty($res) || $res['status'] == 'exception') { // EXCEPTION

                $issue = !empty($res['data']) ? $res['data'] : 'Res Was False';
                Log::warning('[FacebookLibrary][downloadReport][' . $account_id . '][' . $jobId . '] - Error downloading report - ' . json_encode($res));
                $this->error = 'Error downloading report - ' . $issue;
            }
            return $res;
        } else if (in_array($status, ['job running', 'job started'])) { // JOB RUNNING, JOB STARTED
            Log::info("[FacebookLibrary][downloadReport][{$jobId}][{$account_id}] The report is still in progress ({$status_desc->async_percent_completion}%)");
        } else if (in_array($status, ['job failed', 'job not started'])) { // JOB FAILED, JOB NOT STARTED
            Log::warning("[FacebookLibrary][downloadReport] Something wrong with report id {$jobId} for account {$account_id}. Status: {$status_desc->async_status}");
        } else { // NOT RECOGNIZED
            Log::warning("[FacebookLibrary][downloadReport][{$jobId}][{$account_id}] Status not recognized: {$status_desc}");
            $status = 'not recognized';
        }

        return ['status' => $status];
    }

    public function requestCampaignSummaryReport($account_id, string $date_start, string $date_end = '')
    {
        $this->error = '';
        $client = new Client(['verify' => false]);
        /*-- Get a client so we can work with FB API --*/
        $fbToken = $this->getAccessToken();
        if (empty($fbToken)) {
            $this->error = (object) [
                'status' => false,
                'error' => 'Access Token not found',
                'message' => 'Access Token not found'
            ];
            Log::warning("[FacebookLibrary] {$this->error->message}");
            return false;
        }

        //
        // Build it up all information needed and if everything is ok, request a new
        // report_run_id which will be used for starting a new download/import process.
        //
        $time_range = new \stdClass();
        $time_range->since = $date_start;

        if (!empty($date_end)) {
            $time_range->until = $date_end;
        } else {
            $time_range->until = $date_start;
        }

        $filterSpend = (object)[];
        $filterSpend->field = 'spend';
        $filterSpend->operator = 'GREATER_THAN';
        $filterSpend->value = '0';

        $filterAdStatus = (object)[];
        $filterAdStatus->field = 'ad.effective_status';
        $filterAdStatus->operator = 'IN';
        $filterAdStatus->value = ['ARCHIVED', 'ACTIVE', 'PAUSED', 'DELETED', 'CAMPAIGN_PAUSED', 'ADSET_PAUSED', 'PENDING_REVIEW', 'DISAPPROVED', 'PREAPPROVED', 'PENDING_BILLING_INFO'];

        $filterCampaignStatus = (object)[];
        $filterCampaignStatus->field = 'campaign.effective_status';
        $filterCampaignStatus->operator = 'IN';
        $filterCampaignStatus->value = ['ARCHIVED', 'ACTIVE', 'PAUSED', 'DELETED', 'CAMPAIGN_PAUSED', 'ADSET_PAUSED', 'PENDING_REVIEW', 'DISAPPROVED', 'PREAPPROVED', 'PENDING_BILLING_INFO'];

        $filterAdSetStatus = (object)[];
        $filterAdSetStatus->field = 'adset.effective_status';
        $filterAdSetStatus->operator = 'IN';
        $filterAdSetStatus->value = ['ARCHIVED', 'ACTIVE', 'PAUSED', 'DELETED', 'CAMPAIGN_PAUSED', 'ADSET_PAUSED', 'PENDING_REVIEW', 'DISAPPROVED', 'PREAPPROVED', 'PENDING_BILLING_INFO'];

        $filtering = [$filterSpend /*, $filterAdStatus, $filterCampaignStatus, $filterAdSetStatus*/];


        $fields = [
            'account_id',
            'account_name',
            'account_currency',
            'campaign_id',
            'campaign_name',
            'conversions',
            'conversion_values',
            'clicks',
            'impressions',
            'inline_link_clicks',
            'outbound_clicks',
            'spend',
        ];
        $breakdowns = [];


        $data = [
            'access_token'  => $fbToken,
            'breakdowns'    => json_encode($breakdowns),
            'fields'        => json_encode($fields),
            'filtering'     => json_encode($filtering),
            'limit'         => 250,
            'level'         => 'campaign',
            'sort'          => json_encode(['date_start_ascending']),
            'time_range'    => json_encode($time_range)
        ];


        /*-- Send a post requesting reportId --*/
        try {
            $url = $this->base_api_url . '/act_' . $account_id . '/insights';
            // Log::info('[FacebookLibrary][URL DATA]' . json_encode($data));

            // Log::info('[FacebookLibrary][URL]' . $url.'?'.http_build_query($data));
            $res = $client->get($url, ['query' => $data, 'debug' => false]);
            $res = json_decode($res->getBody());

            if (empty($res)) {
                Log::warning("[FacebookLibrary] Unable to request report on Account {$account_id}");
                $this->error = "[FacebookLibrary] Unable to request report on Account {$account_id}";
                return false;
            } elseif (!empty($res->error)) {
                Log::warning("[FacebookLibrary] {$res->error->message}");
                $this->error = "[FacebookLibrary] {$res->error->message}";
                return false;
            }

            Log::info("[FacebookLibrary] Report requested with success - Account {$account_id}");
            return $res;
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $r = $e->getResponse();
            $res = json_decode($r->getBody());
            Log::warning("[FacebookLibrary] " . $res->error->message ?? $e->getMessage());
            $this->error = "[FacebookLibrary] " . $res->error->message ?? $e->getMessage();

            return false;
        } catch (\Exception $e) {
            Log::warning("[FacebookLibrary] {$e->getMessage()}");
            $this->error = "[FacebookLibrary] {$e->getMessage()}";

            return false;
        }
    }

    public function requestReport($account_id, string $date_start, string $date_end = '')
    {
        $this->error = '';
        $client = new Client(['verify' => false]);
        /*-- Get a client so we can work with FB API --*/
        $fbToken = $this->getAccessToken();
        if (empty($fbToken)) {
            $this->error = (object) [
                'status' => false,
                'error' => 'Access Token not found',
                'message' => 'Access Token not found'
            ];
            Log::warning("[FacebookLibrary] {$this->error->message}");
            return false;
        }

        //
        // Build it up all information needed and if everything is ok, request a new
        // report_run_id which will be used for starting a new download/import process.
        //
        $time_range = new \stdClass();
        $time_range->since = $date_start;

        if (!empty($date_end)) {
            $time_range->until = $date_end;
        } else {
            $time_range->until = $date_start;
        }

        $filterSpend = (object)[];
        $filterSpend->field = 'spend';
        $filterSpend->operator = 'GREATER_THAN';
        $filterSpend->value = '0';

        $filterAdStatus = (object)[];
        $filterAdStatus->field = 'ad.effective_status';
        $filterAdStatus->operator = 'IN';
        $filterAdStatus->value = ['ARCHIVED', 'ACTIVE', 'PAUSED', 'DELETED', 'CAMPAIGN_PAUSED', 'ADSET_PAUSED', 'PENDING_REVIEW', 'DISAPPROVED', 'PREAPPROVED', 'PENDING_BILLING_INFO'];

        $filterCampaignStatus = (object)[];
        $filterCampaignStatus->field = 'campaign.effective_status';
        $filterCampaignStatus->operator = 'IN';
        $filterCampaignStatus->value = ['ARCHIVED', 'ACTIVE', 'PAUSED', 'DELETED', 'CAMPAIGN_PAUSED', 'ADSET_PAUSED', 'PENDING_REVIEW', 'DISAPPROVED', 'PREAPPROVED', 'PENDING_BILLING_INFO'];

        $filterAdSetStatus = (object)[];
        $filterAdSetStatus->field = 'adset.effective_status';
        $filterAdSetStatus->operator = 'IN';
        $filterAdSetStatus->value = ['ARCHIVED', 'ACTIVE', 'PAUSED', 'DELETED', 'CAMPAIGN_PAUSED', 'ADSET_PAUSED', 'PENDING_REVIEW', 'DISAPPROVED', 'PREAPPROVED', 'PENDING_BILLING_INFO'];

        $filtering = [$filterSpend /*, $filterAdStatus, $filterCampaignStatus, $filterAdSetStatus*/];


        $fields = [
            'account_id',
            'account_name',
            'account_currency',
            'campaign_id',
            'cost_per_action_type',
            'cost_per_conversion',
            'cost_per_estimated_ad_recallers',
            'cost_per_inline_link_click',
            'cost_per_inline_post_engagement',
            'cost_per_outbound_click',
            'cost_per_thruplay',
            'cost_per_unique_action_type',
            'cost_per_unique_click',
            'cost_per_unique_inline_link_click',
            'cost_per_unique_outbound_click',
            'conversions',
            'conversion_values',
            'clicks',
            'impressions',
            'inline_link_clicks',
            'inline_link_click_ctr',
            'inline_post_engagement',
            'optimization_goal',
            'outbound_clicks',
            'outbound_clicks_ctr',
            'spend',
            'social_spend',
            'reach',
            'quality_ranking',
            'website_ctr',
            'website_purchase_roas',
            'date_start',
            'date_stop',
            'frequency',
            'buying_type',
            'attribution_setting',
            'adset_id',
            'ad_name',
            'ad_id',
            'actions'
        ];
        $breakdowns = [];


        $data = [
            'access_token'  => $fbToken,
            'breakdowns'    => json_encode($breakdowns),
            'fields'        => json_encode($fields),
            'filtering'     => json_encode($filtering),
            'level'         => 'ad',
            'sort'          => json_encode(['date_start_ascending']),
            'time_range'    => json_encode($time_range)
        ];


        /*-- Send a post requesting reportId --*/
        try {
            $url = $this->base_api_url . '/act_' . $account_id . '/insights';
            // Log::info('[FacebookLibrary][URL DATA]' . json_encode($data));

            // Log::info('[FacebookLibrary][URL]' . $url.'?'.http_build_query($data));
            $res = $client->post($url, ['query' => $data, 'debug' => false]);
            $res = json_decode($res->getBody());

            if (empty($res)) {
                Log::warning("[FacebookLibrary] Unable to request report on Account {$account_id}");
                $this->error = "[FacebookLibrary] Unable to request report on Account {$account_id}";
                return false;
            } elseif (!empty($res->error)) {
                Log::warning("[FacebookLibrary] {$res->error->message}");
                $this->error = "[FacebookLibrary] {$res->error->message}";
                return false;
            }

            Log::info("[FacebookLibrary] Report requested with success - Account {$account_id}");
            return $res->report_run_id;
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $r = $e->getResponse();
            $res = json_decode($r->getBody());
            Log::warning("[FacebookLibrary] " . $res->error->message ?? $e->getMessage());
            $this->error = "[FacebookLibrary] " . $res->error->message ?? $e->getMessage();

            return false;
        } catch (\Exception $e) {
            Log::warning("[FacebookLibrary] {$e->getMessage()}");
            $this->error = "[FacebookLibrary] {$e->getMessage()}";

            return false;
        }
    }

    private function downloadFile($account_id, $jobId)
    {
        $client = new Client(['verify' => false]);
        // Get a client so we can work with FB API
        $fbToken = $this->getAccessToken();
        if (empty($fbToken)) {
            Log::warning("[FacebookLibrary] Empty Token, Unable to request report on Account {$account_id}");
            return false;
        }

        Log::info('[FacebookLibrary] Downloading data for JobId: ' . $jobId . ', account id: ' . $account_id);

        $data = [
            'access_token' => $fbToken,
            'limit'        => 500
        ];

        // Data for process
        $return_data = [];

        // Request report download
        $url = $this->base_api_url . '/' . $jobId . '/insights?' . http_build_query($data);


        do {
            $res = $client->get($url, ['debug' => false]);
            $res = json_decode($res->getBody());

            // Check if request's been successful
            if ($res === false) {
                Log::error("[FacebookLibrary][{$jobId}] Download failed");
                return false;
            }

            $return_data = array_merge($return_data, $res->data);

            if (!empty($res->paging->next)) {
                $url = $res->paging->next;
            } else {
                $url = '';
            }
        } while (!empty($url));


        /*-- All data return to process --*/
        return ['status' => 'success', 'data' => $return_data];
    }
}
