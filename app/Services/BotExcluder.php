<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class BotExcluder
{
    /** @var array $excludedUserAgents Array of user agents from config file */
    private $excludedUserAgents = null;

    /** @var array $trackWhitelistedIps Array of whitelisted IPs */
    private $trackWhitelistedIps = null;

    /** @var array $trackWhitelistedUserAgents Array of whitelisted user agent strings */
    private $trackWhitelistedUserAgents = null;

    /** @var array $whitelistedIps Array of whitelisted IPs */
    private $whitelistedIps = null;

    /** @var array $whitelistedUserAgents Array of whitelisted user agent strings */
    private $whitelistedUserAgents = null;

    /** @var array $excludedIps Array of what's blocked */
    private $whatsBlocked = null;

    public static $intCacheExcludedIp = array();
    public static $intCacheExcludedUa = array();

    public static $looper = 0;


    public static $ipList;

    protected $cache_id = 'bot-excluder-ip-check-cache';
    protected $cache_ttl = 10800;

    /* Constants */
    const BLOCK_REASON_IP = 'IP';
    const BLOCK_REASON_USER_AGENT = 'User-Agent';

    public function __construct()
    {
        $this->excludedUserAgents         = config('bot-excluder.excluded_user_agents')        ?? null;
        $this->trackWhitelistedIps        = config('bot-excluder.track_whitelist.ips')         ?? null;
        $this->trackWhitelistedUserAgents = config('bot-excluder.track_whitelist.user_agents') ?? null;
        $this->whitelistedIps             = config('bot-excluder.whitelist.ips')               ?? null;
        $this->whitelistedUserAgents      = config('bot-excluder.whitelist.user_agents')       ?? null;
    }

    public function getWhatsBlocked()
    {
        return $this->whatsBlocked;
    }

        /**
     * Verify if the given IP address and/or User-Agent are from a bot.
     * @param string $ip
     * @param string $userAgent
     * @return bool true if is a bot
     */
    public function isBot($ip, $userAgent)
    {
        return ($this->isExcluded($ip, $userAgent) && !$this->isWhitelisted($ip, $userAgent));
    }

    /**
     * Verify if the given IP address and/or User-Agent are excluded for some reason.
     * @param string $ip
     * @param string $userAgent
     * @return bool true if is excluded
     */
    public function isExcluded($ip, $userAgent)
    {
        $this->resetWhatsBlocked();

        // if (empty($this->excludedUserAgents) && empty($this->excludedUserIps)) {
        //     //\Debugbar::warning('IP/Useragent empty list');
        //     return false;
        // }
        if (!empty(self::$intCacheExcludedIp[$ip])) {
            self::$intCacheExcludedIp[$ip] = true;
            $this->setWhatsBlockedIP();
            return true;
        }
        if (!empty(self::$intCacheExcludedUa[$userAgent])) {
            self::$intCacheExcludedUa[$userAgent] = true;
            $this->setWhatsBlockedUA();
            return true;
        }
        if (isset(self::$intCacheExcludedIp[$ip]) && isset(self::$intCacheExcludedUa[$userAgent])) {
            return false; //if they are set but the two check above fails the values is false for both
        }

        if (!empty($this->excludedUserAgents)) {
            foreach ($this->excludedUserAgents as $pattern) {
                if (preg_match('/' . strtolower($pattern) . '/', strtolower($userAgent))) {
                    $this->setWhatsBlockedUA();
                    return true;
                }
            }
        }
        $ret = $this->isIpExcludedFromDB($ip, $userAgent);
        if ($ret == true) {
            self::$intCacheExcludedIp[$ip] = true;
            $this->setWhatsBlockedIP();
            return $ret;
        }
        self::$intCacheExcludedIp[$ip] = false;
        self::$intCacheExcludedUa[$userAgent] = false;
        return false;
    }

    /**
     * Verify if the given IP address and/or User-Agent is whitelisted.
     * @param string $ip
     * @param string $userAgent
     * @return bool true if is whitelisted
     */
    public function isWhitelisted($ip, $userAgent)
    {
        if (empty($this->whitelistedUserAgents) && empty($this->whitelistedIps)) {
            return false;
        }

        if (!empty($this->whitelistedIps)) {
            foreach ($this->whitelistedIps as $pattern) {
                if (preg_match($pattern, $ip)) {
                    return true;
                }
            }
        }

        if (!empty($this->whitelistedUserAgents)) {
            foreach ($this->whitelistedUserAgents as $pattern) {
                if (preg_match($pattern, $userAgent)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Verify if the given IP address and/or User-Agent is track whitelisted.
     * @param string $ip
     * @param string $userAgent
     * @return bool true if is whitelisted
     */
    public function isTrackWhitelisted($ip, $userAgent)
    {
        if (empty($this->trackWhitelistedUserAgents) && empty($this->trackWhitelistedIps)) {
            return false;
        }

        if (!empty($this->trackWhitelistedIps)) {
            foreach ($this->trackWhitelistedIps as $pattern) {
                if (preg_match($pattern, $ip)) {
                    return true;
                }
            }
        }

        if (!empty($this->trackWhitelistedUserAgents)) {
            foreach ($this->trackWhitelistedUserAgents as $pattern) {
                if (preg_match($pattern, $userAgent)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Set whatsBlocked to User-Agent
     * @param void
     * @return void
     */
    private function setWhatsBlockedUA()
    {
        $this->whatsBlocked = self::BLOCK_REASON_USER_AGENT;
    }

    /**
     * Set whatsBlocked to IP
     * @return void
     */
    private function setWhatsBlockedIP()
    {
        $this->whatsBlocked = self::BLOCK_REASON_IP;
    }

    /**
     * Reset whatsBlocked to null
     * @param void
     * @return void
     */
    private function resetWhatsBlocked()
    {
        $this->whatsBlocked = null;
    }

    /**
     *
     * @param string $ip
     * $param string $userAgent
     * @return bool
     */
    private function isIpExcludedFromDB($ip, $userAgent = null)
    {
        return false; //temporary
        self::$looper = 0;

        if ($this->isTrackWhitelisted($ip, $userAgent)) {
            //\Debugbar::info('IP/Useragent Whitelisted');
            return false;
        }

        if(request()->has('botc') && request()->get('botc') =='clear') {
            Cache::forget($this->cache_id);
        }

        if (self::$ipList == null) {
            
            self::$ipList = Cache::remember(
                $this->cache_id,
                $this->cache_ttl,
                function () {
                    $sql = 'SELECT start_ip, end_ip FROM ip ORDER BY end_ip ASC, start_ip ASC;';
                    $ret = DB::connection('botexcluder')
                        ->select(DB::raw($sql));
                    $data = ['single' => [], 'range' => []];
                    foreach ($ret as $dr) {
                        if ($dr->start_ip == $dr->end_ip) {
                            $data['single'][$dr->start_ip] = 1;
                        } else {
                            $data['range'][] = [
                                'start_ip' => $dr->start_ip,
                                'end_ip'  => $dr->end_ip
                            ];
                        }
                    }
                    unset($data_range);
                    return $data;
                }
            );
        }
        


        $intIP = ip2long($ip);
        //test for single ip blocked
        if (isset(self::$ipList['single'][$intIP])) {
            return true;
        }
        return $this->_search($intIP, self::$ipList['range']);
    }

    /**
     * Binary Search method
     *
     **/
    private function _search($ipLong, $data)
    {
        //once that the chunk is small we loop it
        if (count($data) < 50) {
            foreach ($data as $el) {
                self::$looper++;
                if ($ipLong >= $el['start_ip'] && $ipLong <= $el['end_ip']) {
                    return true;
                }
            }
            return false;
        }

        $middle = ceil(count($data) / 2);
        $firstHalf = array_slice($data, 0, $middle);
        $secondHalf = array_slice($data, $middle);
        if ($ipLong <= $firstHalf[count($firstHalf) - 1]['end_ip']) {
            self::$looper++;
            //we already know that the ip could be in the first half, not in the second one

            //check if we are lucky and the ip is in the range
            if ($ipLong >= $firstHalf[count($firstHalf) - 1]['start_ip']) {
                return true;
            }

            return $this->_search($ipLong, $firstHalf);
        } else {
            //the iplong could be in the second half
            self::$looper++;
            return $this->_search($ipLong, $secondHalf);
        }
    }
}