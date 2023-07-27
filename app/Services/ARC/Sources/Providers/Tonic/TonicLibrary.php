<?php

namespace App\Services\ARC\Sources\Providers\Tonic;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class TonicLibrary
{
    protected $apiUrl = 'https://api.publisher.tonic.com/';
    protected $key;
    protected $secret;

    protected $client;
    protected $requestToken;
    protected $VerifierToken;
    protected $accessToken;

    public function __construct($key, $secret)
    {
        $this->key = $key;
        $this->secret = $secret;

        $this->client = new Client(['verify' => false]);
    }

    public function login()
    {
        $this->requestToken = $this->getRequestToken();
        if (empty($this->requestToken)) return false;

        $this->verifierToken = $this->getVerifierToken();
        if (empty($this->verifierToken)) return false;

        $this->accessToken = $this->getAccessToken();

        return !empty($this->accessToken);
    }

    public function getReport($date)
    {
        try {
            // Request report download
            $request_uri = 'privileged/v3/reports/tracking';
            $url = $this->apiUrl . $request_uri . '?' . http_build_query(['date' => $date]);
            $query = [
                'debug' => false,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => $this->getOAuthHeaderString(
                        'GET',
                        $url,
                        $this->key,
                        $this->secret,
                        $this->accessToken->oauth_token,
                        $this->accessToken->oauth_token_secret
                    )
                ]
            ];
            $response = $this->client->get(
                $url,
                $query
            );
            $response = json_decode($response->getBody());
            return ['status' => true, 'response' => $response];
        } catch (\Exception $e) {
            $err = '[TonicLibrary][getReport]: ' . $e->getMessage();
            Log::warning($err);
            return ['status' => false, 'error' => $err];
        }
    }

    public function getRequestToken()
    {
        try {
            // Request report download
            $request_uri = 'oauth/token/request';
            $url = $this->apiUrl . $request_uri;
            $query = [
                'debug' => false,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => $this->getOAuthHeaderString(
                        'POST',
                        $url,
                        $this->key,
                        $this->secret
                    )
                ]
            ];

            Log::info('[TonicLibrary][AUTH][getRequestToken]' . json_encode([
                'url' => $url,
                'query' => $query,
                'auth' => [
                    'POST',
                    $url,
                    $this->key,
                    $this->secret
                ]
            ]));
            $response = $this->client->post(
                $url,
                $query
            );
            $response = json_decode($response->getBody());
            return $response;
        } catch (\Exception $e) {
            Log::warning('[TonicLibrary][AUTH][getRequestToken]: ' . $e->getMessage());
            return false;
        }
    }

    public function getVerifierToken()
    {
        
        try {
            // Request report download
            $request_uri = 'oauth/token/verify';
            $url = $this->apiUrl . $request_uri;
            $query = [
                'debug' => false,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => $this->getOAuthHeaderString(
                        'POST',
                        $url,
                        $this->key,
                        $this->secret,
                        $this->requestToken->oauth_token,
                        $this->requestToken->oauth_token_secret
                    )
                ]
            ];
            Log::info('[TonicLibrary][AUTH][getVerifierToken]' . json_encode([
                'url' => $url,
                'query' => $query,
                'auth' => [
                    'POST',
                    $url,
                    $this->key,
                    $this->secret,
                    $this->requestToken->oauth_token,
                    $this->requestToken->oauth_token_secret
                ]
            ]));
            $response = $this->client->post(
                $url,
                $query
            );
            $response = json_decode($response->getBody());
            return $response;
        } catch (\Exception $e) {
            Log::warning('[TonicLibrary][AUTH][getVerifierToken]: ' . $e->getMessage());
            return false;
        }
    }

    public function getAccessToken()
    {
        
        try {
            // Request report download
            $request_uri = 'oauth/token/access';
            $url = $this->apiUrl . $request_uri . '?' . http_build_query(['oauth_verifier' => $this->verifierToken->oauth_verifier]);
            
            $query = [
                'debug' => false,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => $this->getOAuthHeaderString(
                        'POST',
                        $url,
                        $this->key,
                        $this->secret,
                        $this->requestToken->oauth_token,
                        $this->requestToken->oauth_token_secret
                    )
                ]
            ];
            Log::info('[TonicLibrary][AUTH][getAccessToken]' . json_encode([
                'url' => $url,
                'query' => $query,
                'auth' => [
                        'POST',
                        $url,
                        $this->key,
                        $this->secret,
                        $this->requestToken->oauth_token,
                        $this->requestToken->oauth_token_secret
                ]
            ]));
            $response = $this->client->post(
                $url,
                $query
            );
            $response = json_decode($response->getBody());
            Log::info('[TonicLibrary][AUTH][getAccessToken]' . json_encode($response));
            return $response;
        } catch (\Exception $e) {
            Log::warning('[TonicLibrary][AUTH][getAccessToken]: ' . $e->getMessage());
            return false;
        }
    }

    public function getOAuthHeaderString(
        string $httpMethod,
        string $url,
        string $oauthConsumerKey,
        string $oauthConsumerSecret,
        string $oauthToken = null,
        string $oauthTokenSecret = null
    ): string {
        $oauthTimestamp = (string)time();
        $oauthNonce = md5("pubtonic_api_nonce" . microtime());

        $parameters = [
            'oauth_consumer_key' => rawurlencode($oauthConsumerKey),
            'oauth_nonce' => rawurlencode($oauthNonce),
            'oauth_signature_method' => rawurlencode("HMAC-SHA1"),
            'oauth_timestamp' => rawurlencode($oauthTimestamp),
            'oauth_version' => rawurlencode("1.0")
        ];

        if (!is_null($oauthToken))
            $parameters['oauth_token'] = rawurlencode($oauthToken);

        $urlParsed = parse_url($url);
        $urlQuery = [];
        if (isset($urlParsed['query'])) {
            $url = str_replace("?" . $urlParsed['query'], "", $url);
            parse_str($urlParsed['query'], $urlQuery);
        }
        foreach ($urlQuery as $key => $value) {
            $parameters[$key] = $value;
        }

        ksort($parameters);

        $signatureBaseString = strtoupper($httpMethod) . '&' . rawurlencode($url) . '&' . rawurlencode(http_build_query($parameters));

        $signatureKey = rawurlencode($oauthConsumerSecret) . "&" . rawurlencode((string)$oauthTokenSecret);

        $signature = rawurlencode(base64_encode(hash_hmac('SHA1', $signatureBaseString, $signatureKey, true)));

        $parameters['oauth_signature'] = $signature;

        foreach ($urlQuery as $key => $value) {
            unset($parameters[$key]);
        }

        $headerString = "OAuth ";

        $c = 0;
        foreach ($parameters as $key => $value) {
            if ($c !== 0) {
                $headerString .= ",";
            }
            $headerString .= $key . "=\"{$value}\"";
            $c++;
        }

        return $headerString;
    }
}
