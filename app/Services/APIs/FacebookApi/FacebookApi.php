<?php

namespace App\Services\APIs\FacebookApi;

use Arr;
use Exception;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Log;

/**
 * Class FacebookApi
 * @package FacebookApi
 */
class FacebookApi
{
    protected string $server;

    protected string $accessToken;

    protected int $attempts = 5;

    protected float $timeout = 5;

    protected int $waitings = 1000;

    protected int $sleepApproximation = 2;


    /**
     * FacebookApi constructor.
     */
    public function __construct()
    {
        $this->server = config('services.facebook.api_base', 'https://graph.facebook.com/16.0');
    }

    public function setAccessToken(string $accessToken)
    {
        $this->accessToken = $accessToken;
    }

    public function setAttempts(int $attempts)
    {
        $this->attempts = $attempts;
    }

    public function setTimeout(int $timeout)
    {
        $this->timeout = $timeout;
    }

    public function get(string $resource, array $data = []): Response
    {
        return $this->manageServerErrors(function () use ($resource, $data) {

            $r = Http::asForm()
                ->get($this->server . '/' . $resource, $this->sanitizeData($data)->toArray())
                ->throw();
            // Log::debug("[" . static::class . "][" . __FUNCTION__ . "] GET usage report: usage {$this->getUsage($r)->toJson()} - resource {$resource} - data {$this->sanitizeData($data)->toJson()}");
            return $r;
        });
    }

    public function post(string $resource, array $data, array $files = []): Response
    {
        return $this->manageServerErrors(function () use ($resource, $data, $files) {

            $r = Http::asForm()
                ->attach($files)
                ->post($this->server . '/' . $resource, $this->sanitizeData($data)->toArray())
                ->throw();
            //Log::debug("[" . static::class . "][" . __FUNCTION__ . "] POST usage report: usage {$this->getUsage($r)->toJson()} - resource {$resource} - data {$this->sanitizeData($data)->toJson()}");
            return $r;
        });
    }

    protected function manageServerErrors(callable $code)
    {
        for ($attempt = 0; $attempt < $this->attempts; $attempt++) {
            try {
                for ($waiting = 0; $waiting < $this->waitings; $waiting++) {
                    try {

                        // Call code
                        return $code();
                    } catch (RequestException $e) {
                        // exception not of my interest
                        if ($e->response['error']['code'] != 80004) {
                            throw $e;
                        }
                        $usage = $this->getUsage($e->response);
                        $callsLimitAccount = $usage->keys()->first();
                        $estimated_time_to_regain_access = $usage[$callsLimitAccount][0]['estimated_time_to_regain_access'];
                        $waitTime = $estimated_time_to_regain_access + $this->sleepApproximation;
                        Log::debug("[" . static::class . "][" . __FUNCTION__ . "] 'too many calls' limit reached on account {$callsLimitAccount}, waiting {$waitTime} minutes to regain access...");
                        sleep($waitTime * 60);
                    }
                }
                throw $e;
            } catch (RequestException $e) {
                // exception not of my interest
                if ($e->is_transient ?? false == false) {
                    throw $e;
                }
                Log::debug("[" . static::class . "][" . __FUNCTION__ . "] transient error encountered, waiting {$this->timeout} seconds to retry...");
                usleep($this->timeout * 1000 * 1000);
            }
        }
        throw $e;
    }

    protected function sanitizeData(array $data): Collection
    {
        return collect($data)
            ->map(fn ($value) => !is_scalar($value) ? json_encode($value) : $value)
            ->merge(['access_token' => $this->accessToken ?? throw new Exception('Access token not set.')]);
    }

    protected function getUsage(Response $response): Collection
    {
        return collect(@json_decode($response->headers()['x-business-use-case-usage'][0], true));
    }
}
