<?php

namespace App\Services\ARC\Sources\Providers\AfsByCbs;

use GuzzleHttp\Client;
use Log;
use Storage;

class AfsByCbsLibrary
{

    protected $base_url;
    protected $secret_key;
    protected $site_id;
    protected $client;
    protected $tmp_path;

    protected $error;

    public function __construct($site_id, $secret_key = '')
    {
        if (empty($secret_key)) {
            $this->secret_key = config('arc.sources.afsbycbs.secret_key');
        } else {
            $this->secret_key = $secret_key;
        }
        $this->site_id      = $site_id;
        $this->base_url     = config('arc.sources.afsbycbs.base_url');
        $this->client = new Client(['verify' => false]);
    }

    public function downloadReport($date_begin, $date_end, $destinationFile)
    {
        $params = [
            'id' => '****',
            'siteId' => $this->site_id,
            'start' => $date_begin,
            'end' => $date_end
        ];

        $logged_url = $this->base_url . '?' . http_build_query($params);
        Log::info('[AfsByCbsLibrary][downloadReport]: ' . $logged_url );
        $params['id'] = $this->secret_key;

        try {
            $response = $this->client->get($this->base_url, ['debug' => false, 'query' => $params]);

            if ($response->getStatusCode() != 200) {
                $this->error = [
                    'message' => '[AfsByCbsLibrary][downloadReport][NOT-AUTHORIZED]',
                    'url' => $logged_url,
                    'response' => $response->getBody(),
                    'error' => $response->getStatusCode() . ' :: ' . $response->getBody()
                ];
                return false;
            }
            $ret = json_decode($response->getBody());

            if(!empty($ret->downloadLink)) {

                try {
                    $response = $this->client->request('GET', $ret->downloadLink, ['sink' => $destinationFile]);

                    return true;
                }
                catch(\Exception $e) {
                    $this->error = [
                        'message' => '[AfsByCbsLibrary][downloadReport][DOWNLOAD-ERROR]',
                        'url' => $ret->downloadLink,
                        'response' => $response->getBody(),
                        'error' => 'Unable to download downloadLink'
                    ];
                    return false;
                }

            } else {
                $this->error = [
                    'message' => '[AfsByCbsLibrary][downloadReport][ERROR]',
                    'url' => $logged_url,
                    'response' => $ret,
                    'error' => 'Unable to Find downloadLink'
                ];
                return false;
            }

        } catch (\Exception $e) {
            $this->error = [
                'message' => '[AfsByCbsLibrary][downloadReport][ERROR]',
                'url' => $logged_url,
                'error' => $e->getMessage()
            ];
            return false;
        }
    }

    public function getLastError()
    {
        return $this->error;
    }
}
