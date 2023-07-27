<?php

namespace App\Services\Utils;

use Closure;

class Monolog
{
    public static function getArtisanPrintProcessor( $newThis )
    {
        return Closure::bind( function($record) {
            $outputs = [
                'DEBUG'   => 'comment',
                'INFO'    => 'info',
                'WARNING' => 'warn',
                'ERROR'   => 'error',
            ];
            $output = $outputs[ $record['level_name'] ];

            $this -> { $output } ("[{$record['datetime']}] {$record['channel']}.{$record['level_name']}: {$record['message']}");
            $this -> line('');

            return $record;
        }, $newThis);
    }
}
