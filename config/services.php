<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    'enki-report-athena' => [
        'version' => env('AWS_ATHENA_VERSION'),
        'key' => env('AWS_ATHENA_ACCESS_KEY'),
        'secret' => env('AWS_ATHENA_SECRET'),
        'region' => env('AWS_ATHENA_REGION'),
        'db_name' => env('AWS_ATHENA_DB_NAME'),
        'output_location' => env('AWS_ATHENA_OUTPUT_LOCATION'),
    ],
    'google_translate' => [
        'key' => env('GOOGLE_TRANSLATE_KEY'),
    ],


    'unsplash' => [
        'app_id' => env('UNSPLASH_APP_ID', ''),
        'utm_source' => env('UNSPLASH_UTM_SOURCE', ''),
        'secret' => env('UNSPLASH_SECRET', '')
    ],

];
