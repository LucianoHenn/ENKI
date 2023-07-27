<?php

namespace App\Services;


class Timer
{

    public static $begin;
    public static $end;


    public static function start()
    {
        static::$begin = microtime(true);
    }

    public static function stop()
    {
        static::$end = microtime(true);
    }

    public static function getTime()
    {
        return round((static::$end-static::$begin)*1000);
    }
}