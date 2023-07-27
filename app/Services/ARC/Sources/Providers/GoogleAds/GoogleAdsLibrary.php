<?php

namespace App\Services\ARC\Sources\Providers\GoogleAds;

use Google\Ads\GoogleAds\Lib\Configuration;
use Google\Ads\GoogleAds\Lib\OAuth2TokenBuilder;
use Google\Ads\GoogleAds\Lib\V13\GoogleAdsClient;
use Google\Ads\GoogleAds\Lib\V13\GoogleAdsClientBuilder;
use Google\Ads\GoogleAds\Lib\V13\GoogleAdsException;
use Google\Ads\GoogleAds\Lib\V13\GoogleAdsServerStreamDecorator;
use Google\Ads\GoogleAds\V13\Resources\CustomerClient;
use Google\Ads\GoogleAds\V13\Services\CustomerServiceClient;
use Google\Ads\GoogleAds\V13\Services\GoogleAdsRow;
use Google\Ads\GoogleAds\V13\Resources\Label;
use Google\Ads\GoogleAds\V13\Services\LabelServiceClient;


use Google\ApiCore\ApiException;

use GuzzleHttp\Client;

use Illuminate\Support\Facades\Log;

use Exception;



class GoogleAdsLibrary
{

    protected $googleAdsClient;
    protected $clientCustomerId;
    protected $developerToken;
    protected $oAuth2Credential;
    protected $logged_in = false;
    protected $gads_array_configuration;
    protected $gads_configuration;
    protected $baseUrl = 'https://googleads.googleapis.com';

    protected $errors = [];
    protected $data = [];
    protected $client;



    public function __construct($params = [])
    {
        if (!empty($params['clientCustomerId'])) {
            $this->clientCustomerId = str_replace('-', '', $params['clientCustomerId']);
        } else {
            $this->clientCustomerId = str_replace('-', '', config('arc.sources.googleads.clientCustomerId'));
        }
        if (!empty($params['developerToken'])) {
            $this->developerToken = $params['developerToken'];
        } else {
            $this->developerToken = config('arc.sources.googleads.developerToken');
        }


        $this->login();
    }

    public function login($params = [])
    {
        $this->gads_array_configuration = $this->createIniFromConfig($params = []);
        $this->gads_configuration = new Configuration($this->gads_array_configuration);

        // Generate a refreshable OAuth2 credential for authentication.
        try {
            $this->oAuth2Credential = (new OAuth2TokenBuilder())
                ->from($this->gads_configuration)
                ->build();


            $this->googleAdsClient = (new GoogleAdsClientBuilder())
                ->from($this->gads_configuration)
                ->withOAuth2Credential($this->oAuth2Credential)
                ->withLoginCustomerId($this->clientCustomerId)
                ->build();

            $this->logged_in = true;
        } catch (OAuth2Exception $e) {
            return $this->CheckForOAuth2Errors($e);
        } catch (ValidationException $e) {
            return $this->CheckForOAuth2Errors($e);
        } catch (Exception $e) {
            Log::error("[GoogleAdsLibrary] An error has occurred: " . $e->getMessage());
            return false;
        }
    }

    public function CheckForOAuth2Errors(Exception $raisedException)
    {
        $errorMessage = "[ERROR] An error has occured:";
        if ($raisedException instanceof OAuth2Exception) {
            $errorMessage = "Your OAuth2 Credentials are incorrect.\nPlease see the GetRefreshToken.php example.";
        } elseif ($raisedException instanceof ValidationException) {
            $requiredAuthFields = array('client_id', 'client_secret', 'refresh_token');
            $trigger = $raisedException->GetTrigger();
            if (in_array($trigger, $requiredAuthFields)) {
                $errorMessage = sprintf("Your OAuth2 Credentials are missing the '%s'.\nPlease see GetRefreshToken.php for further information.", $trigger);
            }
        }
        $this->errors[] = $errorMessage;
        Log::error('[GoogleAdsLibrary]' . $errorMessage . "; " . $raisedException->getMessage());
        return false;
    }

    public function isAuthenticated()
    {
        return $this->logged_in;
    }

    private function createIniFromConfig($params = [])
    {
        if (empty($params)) {
            $params = [
                'clientCustomerId' => config('arc.sources.googleads.clientCustomerId'),
                'developerToken'   => config('arc.sources.googleads.developerToken'),
                'clientId'     => config('arc.sources.googleads.clientId'),
                'clientSecret' => config('arc.sources.googleads.clientSecret'),
                'refreshToken' => config('arc.sources.googleads.refreshToken')
            ];
        }
        return [
            'GOOGLE_ADS' => [
                'loginCustomerId' => str_replace('-', '', $params['clientCustomerId']),
                'developerToken'   => $params['developerToken'],
            ],
            'OAUTH2' => [
                'clientId'     => $params['clientId'],
                'clientSecret' => $params['clientSecret'],
                'refreshToken' => $params['refreshToken'],
            ],
            'LOGGING' => [
                'logFilePath' => config('logging.channels.daily.path'),
                'logLevel' => 'ERROR'
            ]
        ];
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getData()
    {
        return $this->data;
    }


    public function getAllAccountsStats($date_begin, $date_end, $excluded_words = ['suspended'])
    {
        $allAccounts = $this->getAllAccounts();



        $accessToken = $this->oAuth2Credential->fetchAuthToken();

        $this->client = new Client(['verify' => false]);
        $accountsToRecheck = [];
        foreach ($allAccounts as $account) {


            if ($account->manager == true) continue;
            foreach ($excluded_words as $word) {
                if (stripos($account->descriptiveName, $word) !== FALSE) continue;
            }

            try {

                $query = 'SELECT segments.date, customer.id, customer.manager, customer.descriptive_name,'
                    . ' customer.currency_code, customer.time_zone, metrics.clicks, metrics.average_cpc, metrics.cost_micros '
                    . ' FROM customer WHERE metrics.clicks > 0'
                    . sprintf(' AND segments.date BETWEEN "%s" AND "%s"', $date_begin, $date_end);


                $headers = [
                    'Content-Type'      => 'application/json',
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $accessToken['access_token'],
                    'developer-token' => $this->developerToken,
                    'login-customer-id' => $this->clientCustomerId
                ];

                $params = [
                    'debug' => false,
                    'headers' => $headers,
                    'json' => [
                        'query' => $query
                    ]
                ];

                $path = '/v9/customers/' . $account->id . '/googleAds:search';

                $res = $this->client->post(
                    $this->baseUrl . $path,
                    $params
                );
                $res = json_decode($res->getBody());

                if (!empty($res->results[0]->metrics)) {
                    $stats = [];
                    foreach ($res->results as $stat) {
                        $stats[$stat->segments->date] = (object) [
                            'clicks' => $stat->metrics->clicks,
                            'costMicros' => $stat->metrics->costMicros,
                            'averageCpcMicros' => $stat->metrics->averageCpc,
                            'cost' => $stat->metrics->costMicros / 1000000,
                            'averageCpc' => $stat->metrics->averageCpc / 1000000,
                        ];
                    }
                    $account->stats = $stats;
                    $accountsToRecheck[] = $account;
                }
            } catch (\GuzzleHttp\Exception\RequestException $e) {
                $txt = $e->getResponse()->getBody();
                if (stripos($txt, 'CUSTOMER_NOT_ENABLED') === FALSE) {
                    Log::warning('[GoogleAdsLibrary][getAllAccountsStats][Exception] ' . $txt);
                    $res = (object)[];
                }
            } catch (\Exception $e) {
                Log::warning('[GoogleAdsLibrary][getAllAccountsStats][Exception] ' . $e->getMessage());
                $res = (object)[];
            }
        }

        return $accountsToRecheck;
    }

    public function getAllAccounts()
    {
        try {
            $accessToken = $this->oAuth2Credential->fetchAuthToken();

            $this->client = new Client(['verify' => false]);

            $query = 'SELECT customer_client.client_customer, customer_client.level,'
                . ' customer_client.manager, customer_client.descriptive_name,'
                . ' customer_client.currency_code, customer_client.time_zone,'
                . ' customer_client.id FROM customer_client WHERE customer_client.level <= 50';

            $headers = [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $accessToken['access_token'],
                'developer-token' => $this->developerToken
            ];


            $params = [
                'debug' => false,
                'headers' => $headers,
                'json' => [
                    'query' => $query
                ]
            ];

            $path = '/v9/customers/' . $this->clientCustomerId . '/googleAds:search';

            $res = $this->client->post(
                $this->baseUrl . $path,
                $params
            );
            $res = json_decode($res->getBody());



            $accounts = [];
            if (!empty($res->results)) {
                foreach ($res->results as $e) {
                    $accounts[] = $e->customerClient;
                }
            }

            return $accounts;
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $txt = $e->getResponse()->getBody();
            Log::warning('[GoogleAdsLibrary][getAllAccounts][Exception] ' . $txt);
            return null;
        } catch (\Exception $e) {
            Log::warning('[GoogleAdsLibrary][getAllAccounts][Exception] ' . $e->getMessage());
            return null;
        }
        return [];
    }

    public function executeCustomQuery($clientCustomerId, $query)
    {
        $data = [];
        $this->errors = [];
        $clientCustomerId = str_replace('-', '', $clientCustomerId);
        $googleAdsServiceClient = $this->googleAdsClient->getGoogleAdsServiceClient();
        try {
            $stream = $googleAdsServiceClient->searchStream($clientCustomerId, $query);

            foreach ($stream->iterateAllElements() as $googleAdsRow) {
                $x = json_decode($googleAdsRow->serializeToJsonString());

                $data[] = $x;
            }

            return $data;
        } catch (GoogleAdsException $googleAdsException) {
            foreach ($googleAdsException->getGoogleAdsFailure()->getErrors() as $error) {
                /** @var GoogleAdsError $error */
                $this->errors[] = sprintf(
                    "%s: %s%s",
                    $error->getErrorCode()->getErrorCode(),
                    $error->getMessage()
                );
            }

            return false;
        } catch (ApiException $apiException) {
            $this->errors[] = sprintf(
                "ApiException was thrown with message '%s'",
                $apiException->getMessage()
            );

            return false;
        }
    }

    public function getCustomerIdCampaigns($clientCustomerId)
    {
        $this->data = [];
        try {
            $clientCustomerId = str_replace('-', '', $clientCustomerId);
            $googleAdsServiceClient = $this->googleAdsClient->getGoogleAdsServiceClient();
            $query = "SELECT customer.id, customer.descriptive_name , bidding_strategy.type, campaign_budget.amount_micros, campaign.target_roas.target_roas, bidding_strategy.id, campaign.bidding_strategy_type, campaign.campaign_budget, campaign.id, campaign.labels, campaign.status, campaign.target_cpa.target_cpa_micros, campaign.name FROM campaign";
            $stream = $googleAdsServiceClient->searchStream($clientCustomerId, $query);

            $customerLabels = $this->getCustomerIdLabels($clientCustomerId);

            foreach ($stream->iterateAllElements() as $googleAdsRow) {
                $x = json_decode($googleAdsRow->serializeToJsonString());
                if (isset($x->campaign)) {
                    if (!empty($x->campaign->labels)) {
                        $labels = [];
                        foreach ($x->campaign->labels as $labelResourceName) {
                            if (isset($customerLabels[$labelResourceName])) {
                                $labels[] =  $customerLabels[$labelResourceName];
                            }
                        }
                        $x->campaign->labels = $labels;
                    }

                    $this->data[] = $x;
                }
            }
            return true;
        } catch (GoogleAdsException $googleAdsException) {
            foreach ($googleAdsException->getGoogleAdsFailure()->getErrors() as $error) {
                /** @var GoogleAdsError $error */
                $this->errors[] = sprintf(
                    "%s: %s",
                    $error->getErrorCode()->getErrorCode(),
                    $error->getMessage()
                );
            }
            return false;
        } catch (ApiException $apiException) {
            $this->errors[] = sprintf(
                "ApiException was thrown with message '%s'",
                $apiException->getMessage()
            );

            return false;
        }
    }

    public function getCustomerIdAds($clientCustomerId)
    {
        try {
            $accessToken = $this->oAuth2Credential->fetchAuthToken();

            $this->client = new Client(['verify' => false]);


            $clientCustomerId = str_replace('-', '', $clientCustomerId);

            $query = "SELECT campaign.id, ad_group_ad.ad.app_ad.descriptions, ad_group_ad.ad.app_ad.headlines, ad_group_ad.ad.app_ad.html5_media_bundles, ad_group_ad.ad.app_ad.images, ad_group_ad.ad.app_ad.mandatory_ad_text, ad_group_ad.ad.app_ad.youtube_videos, ad_group_ad.ad.app_engagement_ad.descriptions, ad_group_ad.ad.added_by_google_ads, ad_group_ad.ad.app_engagement_ad.headlines, ad_group_ad.ad.app_engagement_ad.images, ad_group_ad.ad.app_engagement_ad.videos,            ad_group_ad.ad.device_preference, ad_group_ad.ad.display_upload_ad.display_upload_product_type, ad_group_ad.ad.display_upload_ad.media_bundle, ad_group_ad.ad.display_url, ad_group_ad.ad.expanded_dynamic_search_ad.description, ad_group_ad.ad.expanded_dynamic_search_ad.description2, ad_group_ad.ad.expanded_text_ad.description, ad_group_ad.ad.expanded_text_ad.description2, ad_group_ad.ad.expanded_text_ad.headline_part1, ad_group_ad.ad.expanded_text_ad.headline_part2, ad_group_ad.ad.expanded_text_ad.headline_part3, ad_group_ad.ad.expanded_text_ad.path1, ad_group_ad.ad.expanded_text_ad.path2, ad_group_ad.ad.final_app_urls, ad_group_ad.ad.final_mobile_urls, ad_group_ad.ad.final_url_suffix, ad_group_ad.ad.final_urls,   ad_group_ad.ad.hotel_ad, ad_group_ad.ad.id, ad_group_ad.ad.image_ad.image_url, ad_group_ad.ad.image_ad.mime_type, ad_group_ad.ad.image_ad.name, ad_group_ad.ad.image_ad.pixel_height, ad_group_ad.ad.image_ad.pixel_width, ad_group_ad.ad.image_ad.preview_image_url, ad_group_ad.ad.image_ad.preview_pixel_height, ad_group_ad.ad.image_ad.preview_pixel_width, ad_group_ad.ad.legacy_app_install_ad, ad_group_ad.ad.legacy_responsive_display_ad.accent_color, ad_group_ad.ad.legacy_responsive_display_ad.allow_flexible_color, ad_group_ad.ad.legacy_responsive_display_ad.business_name, ad_group_ad.ad.legacy_responsive_display_ad.call_to_action_text, ad_group_ad.ad.legacy_responsive_display_ad.description, ad_group_ad.ad.legacy_responsive_display_ad.format_setting, ad_group_ad.ad.legacy_responsive_display_ad.logo_image, ad_group_ad.ad.legacy_responsive_display_ad.long_headline, ad_group_ad.ad.legacy_responsive_display_ad.main_color, ad_group_ad.ad.legacy_responsive_display_ad.marketing_image, ad_group_ad.ad.legacy_responsive_display_ad.price_prefix, ad_group_ad.ad.legacy_responsive_display_ad.promo_text, ad_group_ad.ad.legacy_responsive_display_ad.short_headline, ad_group_ad.ad.legacy_responsive_display_ad.square_logo_image, ad_group_ad.ad.legacy_responsive_display_ad.square_marketing_image, ad_group_ad.ad.local_ad.call_to_actions, ad_group_ad.ad.local_ad.descriptions, ad_group_ad.ad.local_ad.headlines, ad_group_ad.ad.local_ad.logo_images, ad_group_ad.ad.local_ad.marketing_images, ad_group_ad.ad.local_ad.path1, ad_group_ad.ad.local_ad.path2, ad_group_ad.ad.local_ad.videos, ad_group_ad.ad.name, ad_group_ad.ad.resource_name, ad_group_ad.ad.responsive_display_ad.accent_color, ad_group_ad.ad.responsive_display_ad.allow_flexible_color, ad_group_ad.ad.responsive_display_ad.business_name, ad_group_ad.ad.responsive_display_ad.call_to_action_text, ad_group_ad.ad.responsive_display_ad.control_spec.enable_asset_enhancements, ad_group_ad.ad.responsive_display_ad.control_spec.enable_autogen_video, ad_group_ad.ad.responsive_display_ad.descriptions, ad_group_ad.ad.responsive_display_ad.format_setting, ad_group_ad.ad.responsive_display_ad.headlines, ad_group_ad.ad.responsive_display_ad.logo_images, ad_group_ad.ad.responsive_display_ad.long_headline, ad_group_ad.ad.responsive_display_ad.main_color, ad_group_ad.ad.responsive_display_ad.marketing_images, ad_group_ad.ad.responsive_display_ad.price_prefix, ad_group_ad.ad.responsive_display_ad.promo_text, ad_group_ad.ad.responsive_display_ad.square_logo_images, ad_group_ad.ad.responsive_display_ad.square_marketing_images, ad_group_ad.ad.responsive_display_ad.youtube_videos, ad_group_ad.ad.responsive_search_ad.descriptions, ad_group_ad.ad.responsive_search_ad.headlines, ad_group_ad.ad.responsive_search_ad.path1, ad_group_ad.ad.responsive_search_ad.path2, ad_group_ad.ad.shopping_comparison_listing_ad.headline, ad_group_ad.ad.shopping_product_ad, ad_group_ad.ad.shopping_smart_ad, ad_group_ad.ad.system_managed_resource_source, ad_group_ad.ad.text_ad.description1, ad_group_ad.ad.text_ad.description2, ad_group_ad.ad.text_ad.headline, ad_group_ad.ad.tracking_url_template, ad_group_ad.ad.type, ad_group_ad.ad.url_collections, ad_group_ad.ad.url_custom_parameters, ad_group_ad.ad.video_ad.in_stream.action_button_label, ad_group_ad.ad.video_ad.in_stream.action_headline,  ad_group_ad.ad.video_ad.out_stream.description, ad_group_ad.ad.video_ad.out_stream.headline, ad_group_ad.ad.video_responsive_ad.call_to_actions, ad_group_ad.ad.video_responsive_ad.companion_banners, ad_group_ad.ad.video_responsive_ad.descriptions, ad_group_ad.ad.video_responsive_ad.headlines, ad_group_ad.ad.video_responsive_ad.long_headlines, ad_group_ad.ad.video_responsive_ad.videos, ad_group_ad.labels, ad_group_ad.ad_strength, ad_group_ad.ad_group, ad_group_ad.policy_summary.approval_status, ad_group_ad.policy_summary.policy_topic_entries, ad_group_ad.policy_summary.review_status, ad_group_ad.resource_name, ad_group_ad.status FROM ad_group_ad WHERE ad_group_ad.status NOT IN ('REMOVED')";


            $headers = [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'login-customer-id' => $this->clientCustomerId,
                'Authorization' => 'Bearer ' . $accessToken['access_token'],
                'developer-token' => $this->developerToken
            ];


            $params = [
                'debug' => false,
                'headers' => $headers,
                'json' => [
                    'query' => $query
                ]
            ];

            $path = '/v13/customers/' . $clientCustomerId . '/googleAds:search';

            $res = $this->client->post(
                $this->baseUrl . $path,
                $params
            );
            $res = json_decode($res->getBody());


            $customerLabels = $this->getCustomerIdLabels($clientCustomerId);



            $data = [];

            while (!empty($res->results)) {

                foreach ($res->results as $e) {
                    if (!empty($e->adGroupAd->labels)) {
                        $labels = [];
                        foreach ($e->adGroupAd->labels as $labelResourceName) {
                            if (isset($customerLabels[$labelResourceName])) {
                                $labels[] =  $customerLabels[$labelResourceName];
                            }
                        }
                        $e->adGroupAd->labels = $labels;
                    }
                    $data[] = $e;
                }



                if (!empty($res->nextPageToken)) {
                    Log::info('[GoogleAdsLibrary][getCustomerIdAds][' . $clientCustomerId . '] Reading New Page');


                    $params = [
                        'debug' => false,
                        'headers' => $headers,
                        'json' => [
                            'query' => $query,
                            'page_token' => $res->nextPageToken
                        ]
                    ];

                    $res = $this->client->post(
                        $this->baseUrl . $path,
                        $params
                    );
                    $res = json_decode($res->getBody());
                } else {
                    $res->results = []; //clearing the set to exit the loop
                }
            }

            return $data;
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $txt = (string)$e->getResponse()->getBody();
            $txt = json_decode($txt);

            $this->errors[] = isset($txt->error->status) ? $txt->error->status : $txt;
            Log::warning('[GoogleAdsLibrary][getCustomerIdAds][ExceptionA] ' . json_encode($txt));
            return null;
        } catch (\Exception $e) {
            $this->errors[] = $e->getMessage();
            Log::warning('[GoogleAdsLibrary][getCustomerIdAds][ExceptionB] ' . $e->getMessage());
            return null;
        }
        return [];
    }

    public function getCustomerIdLabels($clientCustomerId)
    {
        $clientCustomerId = str_replace('-', '', $clientCustomerId);
        $googleAdsServiceClient = $this->googleAdsClient->getGoogleAdsServiceClient();
        $query = "SELECT label.id, label.name, label.resource_name, label.status, label.text_label.background_color, label.text_label.description FROM label";
        $stream = $googleAdsServiceClient->searchStream($clientCustomerId, $query);

        $results = [];
        foreach ($stream->iterateAllElements() as $googleAdsRow) {
            $x = json_decode($googleAdsRow->serializeToJsonString());
            if (isset($x->label)) {
                $results[$x->label->resourceName] = $x->label;
            }
        }

        return $results;
    }

    public function getCustomerIdCampaignsBudgets($clientCustomerId)
    {
        $clientCustomerId = str_replace('-', '', $clientCustomerId);
        $googleAdsServiceClient = $this->googleAdsClient->getGoogleAdsServiceClient();
        $query = "SELECT campaign_budget.name, campaign_budget.id, campaign_budget.amount_micros, campaign.id FROM campaign_budget";
        $stream = $googleAdsServiceClient->searchStream($clientCustomerId, $query);

        $results = [];
        foreach ($stream->iterateAllElements() as $googleAdsRow) {
            $x = json_decode($googleAdsRow->serializeToJsonString());

            if (isset($x->campaignBudget)) {
                $results[$x->campaign->id] = $x->campaignBudget;
            }
        }

        return $results;
    }

    public function getGoogleAdsClient()
    {
        return $this->googleAdsClient;
    }

    public function getReport($clientCustomerId, $date_begin, $date_end, $options = array())
    {
        $this->data = [];
        $clientCustomerId = str_replace('-', '', $clientCustomerId);
        $googleAdsServiceClient = $this->googleAdsClient->getGoogleAdsServiceClient();

        if (empty($options['reportType'])) {
            $options['reportType'] = 'ADGROUP_PERFORMANCE_REPORT';
        }

        if ($options['reportType'] == 'ADGROUP_PERFORMANCE_REPORT') {
            $fields = [
                'segments.date',
                'customer.id',
                'customer.descriptive_name',
                'campaign.id',
                'campaign.name',
                'ad_group.id',
                'ad_group.name',
                'metrics.clicks',
                'segments.device',
                'metrics.conversions',
                'metrics.cost_micros',
                'metrics.impressions',
                'metrics.video_views',
                'customer.currency_code',
                'metrics.average_cpc',
                'ad_group.labels',
                'ad_group.cpc_bid_micros'
            ];
            $table = 'ad_group';
        } elseif ($options['reportType'] == 'PLACEMENT_PERFORMANCE_REPORT') {
            $fields = [
                'segments.date',
                'customer.currency_code',
                'customer.currency_code',
                'customer.time_zone',
                'ad_group.id',
                'ad_group.name',
                'ad_group.status',
                'bidding_strategy.type',
                'campaign.id',
                'campaign.name',
                'campaign.status',
                'ad_group_criterion.placement.url',
                'customer.id',
                'ad_group_criterion.final_mobile_urls',
                'ad_group_criterion.final_urls',
                'ad_group_criterion.criterion_id',
                'ad_group_criterion.status',
                'ad_group_criterion.tracking_url_template',
                'ad_group_criterion.url_custom_parameters',
                'segments.ad_network_type',
                'segments.device',
                'metrics.average_cpc',
                'metrics.clicks',
                'metrics.impressions',
                'metrics.conversions',
                'metrics.conversions_value',
                'metrics.cost_micros',
                'metrics.cost_per_conversion',
                'metrics.video_views '
            ];

            $table = 'managed_placement_view';
        } elseif ($options['reportType'] == 'AD_PERFORMANCE_REPORT') {

            $fields = [
                'segments.date',
                'customer.currency_code',
                'customer.descriptive_name',
                'ad_group.id',
                'ad_group.cpc_bid_micros',
                'ad_group.name',
                'ad_group.status',
                'metrics.average_cpc',
                'campaign.id',
                'campaign.name',
                'campaign.status',
                'metrics.clicks',
                'metrics.conversions',
                'metrics.cost_micros',
                'metrics.cost_per_conversion',
                'ad_group_ad.ad.final_urls',
                'metrics.ctr',
                // 'ad_group_ad.ad.expanded_text_ad.description',
                // 'ad_group_ad.ad.legacy_responsive_display_ad.description',
                // 'ad_group_ad.ad.expanded_dynamic_search_ad.description',
                // 'ad_group_ad.ad.text_ad.description1',
                // 'ad_group_ad.ad.call_ad.description1',
                // 'ad_group_ad.ad.text_ad.description2',
                // 'ad_group_ad.ad.call_ad.description2',
                'segments.device',
                // 'ad_group_ad.ad.display_url',
                'customer.id',
                // 'ad_group_ad.ad.text_ad.headline',
                // 'ad_group_ad.ad.expanded_text_ad.headline_part1',
                // 'ad_group_ad.ad.expanded_text_ad.headline_part2',
                'ad_group_ad.ad.id',
                // 'ad_group_ad.ad.name',
                // 'ad_group_ad.ad.image_ad.image_url',
                // 'segments.keyword.ad_group_criterion',
                // 'segments.keyword.info.match_type',
                // 'segments.keyword.info.text',
                'metrics.impressions',
                'ad_group.labels',
                // 'ad_group_ad.ad.legacy_responsive_display_ad.long_headline',
                'ad_group_ad.status',
                'metrics.video_views'
            ];
            $table = 'ad_group_ad';
        } elseif ($options['reportType'] == 'GEO_PERFORMANCE_REPORT') {

            $fields = [
                'segments.date',
                'customer.id',
                'customer.descriptive_name',
                'campaign.id',
                'campaign.name',
                'ad_group.id',
                'ad_group.name',
                'segments.device',
                'customer.currency_code',
                'metrics.cost_micros',
                'metrics.conversions',
                'metrics.conversions_value',
                'metrics.clicks',
                'metrics.impressions',
                'metrics.ctr',
                'metrics.average_cpc',
                'metrics.video_views',
                'segments.geo_target_city',
                'segments.geo_target_metro',
                'geographic_view.location_type',
                'geographic_view.country_criterion_id'
            ];
            $table = 'geographic_view';
        } elseif ($options['reportType'] == 'CAMPAIGN_PERFORMANCE_REPORT') {

            $fields = [
                'segments.date',
                'customer.id',
                'customer.descriptive_name',
                'campaign.id',
                'campaign.name',
                'segments.device',
                'campaign_budget.amount_micros',
                'customer.currency_code',
                'metrics.cost_micros',
                'metrics.conversions',
                'metrics.conversions_value',
                'metrics.clicks',
                'metrics.impressions',
                'metrics.ctr',
                'metrics.average_cpc',
                'metrics.video_views'
            ];
            $table = 'campaign';
        } elseif ($options['reportType'] == 'KEYWORD_PERFORMANCE_REPORT') {

            $fields = [
                'segments.date',
                'customer.id',
                'customer.descriptive_name',
                'campaign.id',
                'campaign.name',
                'ad_group_criterion.keyword.text',
                'ad_group_criterion.keyword.match_type',
                'ad_group_criterion.labels',
                'ad_group_criterion.status',
                'ad_group.id',
                'ad_group.cpc_bid_micros',
                'ad_group.name',
                'segments.device',
                'customer.currency_code',
                'metrics.cost_micros',
                'metrics.conversions',
                'metrics.conversions_value',
                'metrics.clicks',
                'metrics.impressions',
                'metrics.ctr',
                'metrics.average_cpc',
                'metrics.video_views'
            ];
            $table = 'keyword_view';
        }

        if ($options['reportType'] == 'CUSTOM_QUERY' && !empty($options['customQuery'])) {
            $reportQuery = $options['customQuery'];
        } else {
            $reportQuery = 'SELECT ' . implode(',', $fields) . ' FROM ' . $table;
        }

        $reportQuery .= sprintf(' WHERE segments.date BETWEEN "%s" AND "%s"', $date_begin, $date_end);

        try {

            $customerLabels = $this->getCustomerIdLabels($clientCustomerId);
            $customerCampaignsBudgets = $this->getCustomerIdCampaignsBudgets($clientCustomerId);

            $stream = $googleAdsServiceClient->searchStream($clientCustomerId, $reportQuery);
            foreach ($stream->iterateAllElements() as $googleAdsRow) {
                $x = json_decode($googleAdsRow->serializeToJsonString());


                if (isset($x->campaign)) {
                    $x->campaign->campaignBudget = $customerCampaignsBudgets[$x->campaign->id] ?? null;
                }

                if (!empty($x->adGroup->labels)) {
                    $labels = [];
                    foreach ($x->adGroup->labels as $labelResourceName) {
                        if (isset($customerLabels[$labelResourceName])) {
                            $labels[] =  $customerLabels[$labelResourceName];
                        }
                    }
                    $x->adGroup->labels = $labels;
                }

                if (!empty($x->adGroupCriterion->labels)) {
                    $labels = [];
                    foreach ($x->adGroupCriterion->labels as $labelResourceName) {
                        if (isset($customerLabels[$labelResourceName])) {
                            $labels[] =  $customerLabels[$labelResourceName];
                        }
                    }
                    $x->adGroupCriterion->labels = $labels;
                }

                $this->data[] = $x;
            }

            return true;
        } catch (GoogleAdsException $googleAdsException) {
            foreach ($googleAdsException->getGoogleAdsFailure()->getErrors() as $error) {
                /** @var GoogleAdsError $error */
                $this->errors[] = sprintf(
                    "%s: %s",
                    $error->getErrorCode()->getErrorCode(),
                    $error->getMessage()
                );
            }

            return false;
        } catch (ApiException $apiException) {
            $this->errors[] = sprintf(
                "ApiException was thrown with message '%s'",
                $apiException->getMessage()
            );

            return false;
        }


        return true;
    }
}
