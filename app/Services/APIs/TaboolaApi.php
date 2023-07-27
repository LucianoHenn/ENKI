<?php

namespace App\Services\APIs;

use GuzzleHttp\Client;
use Exception;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


class TaboolaApi
{
    protected string $server;
    protected string $client_id;
    protected string $client_secret;

    private $token;
    private $client;



    public function __construct()
    {
        $this->client = new Client();
        $this->server = config('arc.sources.taboola.api_url', 'https://backstage.taboola.com/backstage');
        $this->client_id  =  config('arc.sources.taboola.client_id');
        $this->client_secret = config('arc.sources.taboola.client_secret');
        $this->getAccessToken();
    }

    private function getAccessToken()
    {
        if ($this->token)
            return $this->token;

        $response = $this->client->request('POST',  $this->server . '/oauth/token', [
            'form_params' => [
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'grant_type' => 'client_credentials'
            ],
            'headers' => [
                'accept' => 'application/json',
                'content-type' => 'application/x-www-form-urlencoded',
            ],
        ]);

        $response = json_decode($response->getBody());

        $this->token = $response->access_token;
    }


    public function get(string $resource, array $data = [])
    {
        $response =  $this->client->request('GET',  $this->server . $resource, [
            'headers' => [
                'accept' => 'application/json',
                'authorization' => 'Bearer ' . $this->getAccessToken()
            ],
        ]);


        // Get the response body as a string
        $responseBody = $response->getBody()->getContents();

        // Decode the JSON response into an associative array
        $responseData = json_decode($responseBody, true);

        return $responseData;
    }

    public function delete(string $resource, array $data = [])
    {



        $response =  $this->client->request('DELETE',  $this->server . $resource, [
            'headers' => [
                'accept' => 'application/json',
                'authorization' => 'Bearer ' . $this->getAccessToken()
            ],
        ]);


        return $response;
    }

    public function post(string $resource, array $data, $contentType = 'application/json')
    {

        try {
            $response =  $this->client->request('POST',  $this->server . $resource, [
                'body' => json_encode($data),
                'headers' => [
                    'accept' => 'application/json',
                    'content-type' => $contentType,
                    'authorization' => 'Bearer ' . $this->getAccessToken()
                ],
            ]);
        } catch (RequestException $e) {
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
        }

        return $response;
    }

    protected function handleError($errorCode)
    {
        // switch($errorCode) {
        //     case "ERR01":
        //         throw new OrganizationNotFoundException();
        //     case "ERR02":
        //         throw new OrganizationNotAuthorizedException();
        //     default:
        //         throw new LsException("Unexpected error $errorCode");
        // }
    }
}
