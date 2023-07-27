<?php


namespace App\Services;

use DB;
use Cache;
use App\Models\ServiceProviderConfig;

class ClientRequestStatusValidator
{
    protected static $client_active_configs;

    public static function getActiveConfigs($client_code, $market, $service_provider)
    {
        if (is_null(static::$client_active_configs)) {

            $cache_id = sha1('getActiveConfigs-' . implode('|', [$client_code, $market, $service_provider]));
            Cache::forget($cache_id);
            static::$client_active_configs = Cache::remember($cache_id, 300, function () use ($client_code, $market, $service_provider) {
                $select = 'clients.secret as secret, service_provider_configs.id as config_id, service_provider_configs.name, service_provider_configs.data';
                    // . 'JOIN service_provider_configs as cfg ON cfg.client_id = c.id '
                    // . 'JOIN service_providers sp ON sp.id = cfg.service_provider_id '
                    // . 'JOIN markets ON markets.id = cfg.market_id';


                $where = 'clients.status = 1 AND markets.status = 1 AND service_providers.status = 1 AND service_provider_configs.status = 1 '
                    . 'AND clients.code = :client_code AND markets.code = :market_code AND (service_providers.name = :provider OR service_providers.code = :provider_code)';

                    DB::enableQueryLog();
                $qBuilder = DB::table('clients')
                    ->selectRaw($select)
                    ->join('service_provider_configs', 'service_provider_configs.client_id', '=', 'clients.id')
                    ->join('service_providers', 'service_provider_configs.service_provider_id', '=', 'service_providers.id')
                    ->join('markets', 'service_provider_configs.market_id', '=', 'markets.id')

                    ->whereRaw($where, [
                        'client_code' => $client_code,
                        'market_code' => $market,
                        'provider' => $service_provider,
                        'provider_code' => $service_provider
                    ]);

                return $qBuilder->get();
            });
        }

        return static::$client_active_configs;
    }

    public static function getConfigData($client_code, $market, $service_provider, $config_id)
    {
        $client_active_configs = static::getActiveConfigs($client_code, $market, $service_provider);


        $active = $client_active_configs->firstWhere('config_id', $config_id);
        if(!is_null($active) && empty($active->processed)) {

            $active->data = json_decode($active->data);
            $active->processed = true;

        }

        return $active;
    }
}
