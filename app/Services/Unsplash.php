<?php

namespace App\Services;

use Unsplash\HttpClient;
use Unsplash\Search;
use Log;

class Unsplash
{

    protected $client;

    public function __construct()
    {
        $appId = config('services.unsplash.app_id');
        $utmSource = config('services.unsplash.utm_source');

        if (!$appId || !$utmSource) {
            throw new \RuntimeException(
                'The UNSPLASH_APP_ID and UNSPLASH_UTM_SOURCE must be provided.'
            );
        }

        $this->client = HttpClient::init([
            'applicationId' => $appId,
            'utmSource' => $utmSource
        ]);
    }

    public function searchPhotos($inputs)
    {
        try {
            $search = $inputs['keywords'];
            $page = 1;
            $per_page = $inputs['perPage'] ?? 12;
            $orientation = 'squarish';

            $photos = Search::photos($search, $page, $per_page, $orientation);

            $photos = $photos->getResults();

            $data = [];

            foreach ($photos as $photo) {
                array_push($data, ['id' => $photo['id'],  'url' => $photo['urls']['regular']] + $photo);
            }

            return $data;
        } catch (\Exception $e) {
            return false;
        }
    }
}
