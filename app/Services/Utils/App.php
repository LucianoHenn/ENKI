<?php

namespace App\Services\Utils;


use Illuminate\Support\Facades\App as BaseApp;

class App extends BaseApp
{
    public static function environment( ...$args )
    {
        $env = parent::environment( ...$args );

        $dictionary = [
            'local' => 'dev',
        ];

        return $dictionary[ $env ] ?? $env;
    }
}
