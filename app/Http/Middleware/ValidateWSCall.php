<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Str;
use App;
use Log;
use App\Services\ClientRequestStatusValidator;
use App\Services\Timer;

class ValidateWSCall
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $unique_call_id = Str::random(40);
        $request->unique_call_id = $unique_call_id;
        $token = $this->getEnkiToken($request);

        if (is_null($token)) {
            Timer::stop();
            $processing_ts = Timer::getTime();
            Log::warning(json_encode([
                'log' => '[WS][V1][RESPONSE]',
                'status' => false,
                'message' => 'No Token Received',
                'processingTimeMillisecond' => $processing_ts,
                'callId' => $request->unique_call_id,
                'callUrl' => url()->full()
            ]));
            return response()->json([
                'status' => false,
                'message' => 'No Token Received',
                'server-time' => gmdate(DATE_W3C),
                'processing-time-milliseconds' => $processing_ts,
                'call_id' => $request->unique_call_id
            ], 401);
        }

        $version = $request->segment(2);
        $client_id = $request->clientId ?? '';
        $market = $request->mkt ?? '';
        $provider = $request->provider ?? '';
        $config_id = $request->configId ?? '';
        $timestamp = $request->timestamp ?? '';


        $chunks = explode(':', $token);

        if($chunks[0] != $client_id) {
            Timer::stop();
            $processing_ts = Timer::getTime();
            Log::warning(json_encode([
                'log' => '[WS][V1][RESPONSE]',
                'status' => false,
                'message' => 'Tempered Token',
                'processingTimeMillisecond' => $processing_ts,
                'callId' => $request->unique_call_id,
                'callUrl' => url()->full()
            ]));
            
            
            return response()->json([
                'status' => false,
                'message' => 'Tempered Token',
                'server-time' => gmdate(DATE_W3C),
                'processing-time-milliseconds' => $processing_ts,
                'call_id' => $request->unique_call_id
            ], 403);
        }


        $config = ClientRequestStatusValidator::getConfigData($client_id, $market, $provider, $config_id);

        if (empty($config)) {
            Timer::stop();
            $processing_ts = Timer::getTime();

            Log::warning(json_encode([
                'log' => '[WS][V1][RESPONSE]',
                'status' => false,
                'message' => 'Client Not Authorized To This Call',
                'processingTimeMillisecond' => $processing_ts,
                'callId' => $request->unique_call_id,
                'callUrl' => url()->full()
            ]));
            return response()->json([
                'status' => false,
                'message' => 'Client Not Authorized To This Call',
                'server-time' => gmdate('Y-m-d H:i:s'),
                'processing-time-milliseconts' => $processing_ts,
                'call_id' => $request->unique_call_id
            ], 401);
        }

        $currentSignature = getSignature(
            $client_id,
            $config->secret,
            $provider,
            $market,
            $config_id,
            $timestamp,
            'ws',
            $version
        );

        if ($currentSignature != $token) {
            Timer::stop();
            $processing_ts = Timer::getTime();

            Log::warning(json_encode([
                'log' => '[WS][V1][RESPONSE]',
                'status' => false,
                'message' => 'Invalid Token',
                'processingTimeMillisecond' => $processing_ts,
                'callId' => $request->unique_call_id,
                'callUrl' => url()->full()
            ]));
            return response()->json([
                'status' => false,
                'message' => 'Invalid Token',
                'server-time' => gmdate('Y-m-d H:i:s'),
                'processing-time-milliseconts' => $processing_ts,
                'call_id' => $request->unique_call_id
            ], 403);
        }

        //VALIDATE TIMESTAMP
        $ts = time();

        if (!App::environment('local') && ($ts - $timestamp) > (config('ws.timestamp_ttl_minutes') * 60)) {
            Timer::stop();
            $processing_ts = Timer::getTime();

            Log::warning(json_encode([
                'log' => '[WS][V1][RESPONSE]',
                'status' => false,
                'message' => 'Expired Request',
                'processingTimeMillisecond' => $processing_ts,
                'callId' => $request->unique_call_id,
                'callUrl' => url()->full()
            ]));
            
            return response()->json([
                'status' => false,
                'message' => 'Expired Request',
                'server-time' => gmdate('Y-m-d H:i:s'),
                'processing-time-milliseconts' => $processing_ts,
                'call_id' => $request->unique_call_id
            ], 419);

        }

        return $next($request);
    }


    private function getEnkiToken($request)
    {
        $header = $request->header('Authorization', '');
        if (Str::startsWith($header, 'Bearer ')) {
            return Str::substr($header, 7);
        }
        return null;
    }
}
