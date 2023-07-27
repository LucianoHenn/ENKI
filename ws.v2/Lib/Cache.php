<?php

namespace WsV2\Lib;


class Cache
{
    protected $memcached;

    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    protected function connect()
    {
        if(is_null($this->memcached)) {
            $this->memcached = new \Memcached();

            foreach($this->config['servers'] as $server) {
                $this->memcached->addServer(trim($server['host']), trim($server['port']));
            }
        }
    }

    public function get($key)
    {
        $this->connect();
        return $this->memcached->get($key);
    }

    public function set($key, $var, $ttl = -1)
    {
        $this->connect();
        if($ttl = -1) {
            return $this->memcached->set($key, $var);
        }
        return $this->memcached->set($key, $var, $ttl);
    }
}