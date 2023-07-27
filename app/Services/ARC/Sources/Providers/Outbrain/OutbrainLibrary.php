<?php

namespace App\Services\ARC\Sources\Providers\Outbrain;

use Illuminate\Support\Facades\Storage;
use GuzzleHttp\Client;
use Log;
//
// Documentation:
// https://amplifyv01.docs.apiary.io/#reference/performance-reporting
//

class OutbrainLibrary
{
    private $username;
    private $password;
    private $basic_url = 'https://api.outbrain.com/amplify/v0.1/';

    public $tokenFile;

    private $data;
    private $error;

    public function __construct()
    {
        $outbrain_config = config('arc.sources.outbrain');

        $this->username = $outbrain_config['username'];
        $this->password = $outbrain_config['password'];

        $tokenDir = config('arc.tokens_path') . '/Outbrain';
        Storage::disk('system')->makeDirectory($tokenDir);

        $this->tokenFile = $tokenDir . '/' . $this->username . '.txt';

        $this->client = new Client([
            'base_uri' => $this->basic_url,
            'verify' => false,
            'headers' => ['Content-Type' => 'application/json']
        ]);
    }

    public function authenticate()
    {
        //we make re-auth if the token file does not exist 
        //or if the token file is older than one day
        if (!file_exists($this->tokenFile) || (time() - filemtime($this->tokenFile)) > 86400) {

            // Create a client with a base URL
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->basic_url . 'login');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_HEADER, FALSE);
            curl_setopt($ch, CURLOPT_USERPWD, "$this->username:$this->password");
            $response = curl_exec($ch);
            $data = json_decode($response, true);

            if (!isset($data['OB-TOKEN-V1'])) {
                $this->error = 'Token OB-TOKEN-V1 not exist.';
                if (isset($data['message'])) {
                    $this->error .= ' ' . $data['message'];
                }
                return false;
            }

            file_put_contents($this->tokenFile, $data['OB-TOKEN-V1']);
            curl_close($ch);
        }

        return true;
    }

    public function requestPerformanceReport(string $marketers_id, string $date_start, string $date_end)
    {
        if (!$this->authenticate()) {
            return false;
        }

        $token = file_get_contents($this->tokenFile, 'r');

        $offset = 0;
        $limit = 500; // items per page

        $this->error = "";
        $this->data = [];
        $output = [];

        // The request is paginate
        $baseUrl =  $this->basic_url . "reports/marketers/{$marketers_id}/campaigns";

        do {
            $query = array(
                'from' => $date_start,
                'to' => $date_end,
                'limit' => $limit,
                'offset' => $offset,
                'includeArchivedCampaigns' => 'true',
                'includeConversionDetails' => 'true',
            );
            //filter={filter}
            //includeArchivedCampaigns={includeArchivedCampaigns}
            //budgetId={budgetId}
            //campaignId={campaignId}
            //includeConversionDetails={includeConversionDetails}
            //conversionsByClickDate={conversionsByClickDate}

            //$report_url = $this->basic_url . "reports/marketers/$this->mid/performanceByCampaign/?" . http_build_query($query);
            $report_url = $baseUrl . '?' . http_build_query($query);
            //var_dump(http_build_query($query));

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $report_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_HEADER, FALSE);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "OB-TOKEN-V1:$token"
            ));

            $response = curl_exec($ch);
            if ($errno = curl_errno($ch)) {
                $error_message = curl_strerror($errno);
                $this->error = $error_message;
                return false;
                //echo "cURL error ({$errno}):\n {$error_message}";
            }

            $data = json_decode($response, true);

            if (isset($data['message'])) {
                $this->error = $data['message'];
                return false;
            } else {
                // Merge results
                if (empty($output)) {
                    $output = $data;
                } else {
                    $output['results'] = array_merge($output['results'], $data['results']);
                }
            }

            // next round!
            $offset += $limit;
        } while (count($output['results']) < $output['totalResults']);

        $this->data = $output;
        return true;
    }

    public function getMarketers()
    {
        if (!$this->authenticate()) {
            return false;
        }

        $token = file_get_contents($this->tokenFile, 'r');
        try {


            $res = $this->client->get(
                'marketers', [
                    'headers' => [
                        'OB-TOKEN-V1' => $token
                    ], 'debug' => false
                ]);
            $response = json_decode($res->getBody());

            return(object) [ 
                'http_code' => $res->getStatusCode(),
                'response' => $response
            ];
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $r = $e->getResponse();
            
            $response = json_decode($r->getBody());
            Log::warning("[OutbrainLibrary][getMarketers] " . $e->getMessage());
            

            return false;
        } catch (\Exception $e) {
            Log::warning("[OutbrainLibrary][getMarketers] {$e->getMessage()}");
            $this->error = "[OutbrainLibrary][getMarketers] {$e->getMessage()}";

            return false;
        }

    }

    public function getResponse()
    {
        return $this->data;
    }

    public function getError()
    {
        return $this->error;
    }
}
