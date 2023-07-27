<?php

/**
 * ARC
 * Automatic Reports Collector file config
 * This file must be edited while listen this song in background ;)

 *
 */

return [
    'downloader' => [
        // Enable the check on broken download?
        'enable_skip_broken_job' => 1, // 1 or 0
        // number of days to skip a broken report
        'skip_broken_job_days' => 1
    ],
    'importer' => [],
    'notifier' => [],
    'checker' => [],

    'sources' => [
        'afsbycbs' => [
            'base_url' => env('AFSBYCBS_BASE_URL'),
            'secret_key' => env('AFSBYCBS_SECRET_KEY'),
        ],
        'bingadsrevenue' => [
            'api_base_url' => env('BINGADSREVENUE_API_BASE_URL'),
            'api_key' => env('BINGADSREVENUE_API_KEY'),
        ],
        'googleads' => [
            'clientCustomerId' => env('GOOGLE_ADS_CLIENT_CUSTOMER_ID'),
            'developerToken'   => env('GOOGLE_ADS_DEVELOPER_TOKEN'),
            'clientId'     => env('GOOGLE_ADS_CLIENT_ID'),
            'clientSecret' => env('GOOGLE_ADS_CLIENT_SECRET'),
            'refreshToken' => env('GOOGLE_ADS_REFRESH_TOKEN')
        ],
        'bingads' => [
            'parent_customer_id' => env('BINGADS_CUSTOMER_ID'),
            'client_id'         => env('BINGADS_CLIENT_ID'),
            'client_secret'     => env('BINGADS_CLIENT_SECRET'),
            'url'               => env('BINGADS_AUTH_URL'),
            'redirect_uri'      => env('BINGADS_REDIRECT_URI'),
            'developer_token'   => env('BINGADS_DEVELOPER_TOKEN'),
        ],
        'exploreads' => [
            'api_url' => env('EXPLORE_ADS_API_URL'),
            'api_url_hourly' => env('EXPLORE_ADS_API_URL_HOURLY'),
            'credentials' => [
                'username' => env('EXPLORE_ADS_USERNAME'),
                'password' => env('EXPLORE_ADS_PASSWORD'),
            ]
        ],
        'yahoo' => [
            'api_url' => env('ARC_YAHOO_REPORTS_URL', ''),
            'api_login_url' => env('ARC_YAHOO_REPORTS_LOGIN_URL', ''),
            'api_client_id' => env('ARC_YAHOO_REPORTS_CLIENT_ID', ''),
            'api_client_secret' => env('ARC_YAHOO_REPORTS_CLIENT_SECRET', ''),
        ],
        'facebook' => [
            'base_api_url' => env('FACEBOOK_API_URL', ''),
            'business_id' => env('FACEBOOK_BUSINESS_ID', ''),
            'access_token' => env('FACEBOOK_API_TOKEN', '')
        ],
        'iac' => [
            'email_credentials' => [
                'server' => env('ARC_IAC_EMAIL_SERVER', 'imap.aruba.it'),
                'username' => env('ARC_IAC_EMAIL_USERNAME', ''),
                'password' => env('ARC_IAC_EMAIL_PASSWORD', ''),
                'protocol' => env('ARC_IAC_EMAIL_SECURE_PROTOCOL', 'ssl'),
                'port' => env('ARC_IAC_EMAIL_PORT', '993')
            ]
        ],
        'taboola' => [
            'api_url' => env('TABOOLA_API_URL', ''),
            'client_id' => env('TABOOLA_CLIENT_ID', ''),
            'client_secret' => env('TABOOLA_CLIENT_SECRET', ''),
        ],
        'tiktok' => [
            'app_id' => env('TIKTOK_APP_ID', ''),
            'app_secret' => env('TIKTOK_APP_SECRET', ''),
            'authorization_url' => env('TIKTOK_ADVERTISER_AUTHORIZATION_URL', ''),
            'base_api_url' => env('TIKTOK_BASE_API_URL', ''),
            'access_token' => env('TIKTOK_ACCESS_TOKEN', ''),
        ],
        'zemanta' => [
            'token_api_url' => 'https://oneapi.zemanta.com/o/token/',
            'base_api_url'  => 'https://oneapi.zemanta.com/rest/v1/',
            'client_id'     => env('ZEMANTA_CLIENT_ID'),
            'client_secret' => env('ZEMANTA_CLIENT_SECRET'),
        ],
        'outbrain' => [
            'username' => env('OUTBRAIN_USERNAME'),
            'password' => env('OUTBRAIN_PASSWORD'),
        ],
    ],
    'local_reports_path' => env('ARC_TMP_PATH', '/tmp/arc/') . 'reports/',
    'tmp_path' => env('ARC_TMP_PATH', '/tmp/arc/'),
    'tokens_path' => env('ARC_TMP_PATH', '/tmp/arc/') . 'tokens/',
    'cookies_path' =>  env('ARC_TMP_PATH', '/tmp/arc/') . 'cookies/',
    'clients_reports_path' =>  env('ARC_TMP_PATH', '/tmp/arc/') . 'clients_reports/',
    'max_days_range_yads_client_reports' => env('MAX_DAYS_RANGE_YADS_CLIENT_REPORTS', 60),
    'cost_vs_explore_ads_report_cache_key_prefix' => env('COST_VS_EXPLORE_CACHE_KEY', 'cost-vs-explore-cache-key')

];
