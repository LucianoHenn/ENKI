<?php

namespace App\Services\Booster\Utils;

class YahooRelated
{
    static private $_valid_markets = [
       'nl', 'au', 'uk', 'it',
       'ca', 'fr', 'de', 'es',
       'in', 'mx', 'ar', 'sg',
       'id', 'qc', 'ph', 'us',
       'br', 'se', 'dk', 'no', 'fi'
    ];
    static private $_last_url = NULL;

    public static function getRelated($query, $numResults, $market = 'it')
    {
        if($market == 'gb') $market = 'uk';
        $result = array();
        if (in_array($market, self::$_valid_markets) !== FALSE) {
            $baseUrl = "http://sugg.{$market}.search.yahoo.net/gossip-{$market}-sayt?";

            $params = array('command' => $query, 'output' => 'js', 'nresults' => $numResults);
            $url = $baseUrl . http_build_query($params);

            self::$_last_url = $url;

            $result = self::_processCurl($url);
        }
        return $result;
    }

    public static function _processCurl($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $data = curl_exec($ch);
        curl_close($ch);
        $dt = @json_decode($data);
        $results = array();
        if (!empty($dt->gossip->results)) {
            foreach ($dt->gossip->results as $k) {
                $results[] = $k->key;
            }
        }
        return $results;
    }

    public static function getUrlCall()
    {
        return self::$_last_url;
    }
}
