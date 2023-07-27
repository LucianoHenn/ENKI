<?php

namespace App\Services;

use DB;
use Jenssegers\Agent\Agent;
use Cache;

class YahooAssociations
{

    protected static $table_prefix = 'yahoo_associations_';

    protected static $db_connection = 'mysql_yahoo_associations';

    protected $user_agent;
    protected $user_ip;
    protected $device;
    protected $device_name;
    protected $device_platform;
    protected $device_browser;
    protected $device_platform_version;
    protected $device_browser_version;
    protected $market;
    protected $config_id;
    protected $sub_id;
    protected $adsQuery;

    protected $sourceUrl;
    protected $sourceUrlQuery;

    protected $enkiServiceParams;


    protected $extraData;

    public function setUserAgent($user_agent)
    {
        $this->user_agent = $user_agent;
        $agent = new Agent();
        $agent->setUserAgent($this->user_agent);

        $this->device = 'desktop';
        if ($agent->isTablet()) {
            $this->device = 'tablet';
        } elseif ($agent->isMobile()) {
            $this->device = 'mobile';
        }
        $this->device_name = $agent->device();
        $this->device_platform = $agent->platform();
        $this->device_browser = $agent->browser();
        $this->device_browser_version = $agent->version($this->device_browser);
        $this->device_platform_version = $agent->version($this->device_platform);

    }

    public function setUserIp($user_ip)
    {
        $this->user_ip = $user_ip;
    }

    public function setMarket($market)
    {
        $this->market = $market;
    }

    public function setConfigId($config_id)
    {
        $this->config_id = $config_id;
    }


    public function setSubId($sub_id)
    {
        $this->sub_id = $sub_id;
    }

    public function setAdsQuery($adsQuery)
    {
        $this->adsQuery = $adsQuery;
    }

    public function setSourceUrl($sourceUrl)
    {
        $this->sourceUrl = $sourceUrl;

        $pu = parse_url($this->sourceUrl, PHP_URL_QUERY);
        if (!empty($pu)) {
            parse_str($pu, $this->sourceUrlQuery);
        }
    }

    public function setEnkiServiceParams($enkiServiceParams)
    {
        $this->enkiServiceParams = $enkiServiceParams;
    }

    public function setExtraData($extraData)
    {
        $this->extraData = $extraData;
    }

    public function getHash()
    {
        return strtolower(sha1(json_encode([
            $this->sub_id,
            $this->config_id
        ])));
    }

    public function getInfoField()
    {
        $info = [
            'ts' => time(),
            'market' => $this->market,
            'user_ip' => $this->user_ip,
            'user_agent' => $this->user_agent,
            'device' => [
                'type' => $this->device,
                'name' => $this->device_name,
                'platform' => $this->device_platform,
                'browser' => $this->device_browser,
                'browser_version' => $this->device_browser_version,
                'platform_version' => $this->device_platform_version,
            ],
            'sub_id' => $this->sub_id,

            'sourceUrl' => $this->sourceUrl,
            'sourceUrlQuery' => $this->sourceUrlQuery,

            'enkiServiceParams' => $this->enkiServiceParams,

            'extraData' => $this->extraData,

            'geo'   => null,

            'ads'   => ['query' => $this->adsQuery]
        ];

        return $info;
    }

    protected static function getTableName($hash)
    {
        $hash = trim($hash);
        if (empty($hash)) {
            return null;
        }
        $x = strtolower(substr($hash, 0, 2));
        return static::$table_prefix . $x;
    }

    public static function save($market, $hash, $info)
    {
        $table_name = static::getTableName($hash);

        DB::connection(static::$db_connection)->insert(
            'insert ignore into ' . $table_name . ' (market, hash, date, info, created_at, updated_at) values (?, ?, ?, ?, ?, ?)',
            [$market, $hash, gmdate('Y-m-d'), json_encode($info), now()->format('Y-m-d H:i:s'), now()->format('Y-m-d H:i:s')]
        );
    }

    public static function get($hash)
    {
        $cache_id = 'yahoo_association_ids_cache_' . $hash;
        $ttl = rand(14400, 24000);

        return Cache::remember(
            $cache_id,
            $ttl,
            function () use ($hash) {
                $table_name = static::getTableName($hash);

                return DB::connection(static::$db_connection)->table($table_name)->select(
                    'hash',
                    'info'
                )->where('hash', $hash)->first();
            }
        );
    }
}
