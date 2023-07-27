<?php

namespace App\Services\ARC\Sources\Providers\Yahoo\Daily;

use App\Services\ARC\Sources\Abstracts\BaseRequest;
use App\Services\ARC\Elements\Identifier;


use App\Services\ARC\Sources\Providers\Yahoo\YahooLibrary;

/**
 * Class YahooDailyRequest
 */
class YahooDailyRequest extends BaseRequest
{
    

    public function getIdentifiers() : array
    {
        // Return the list of identifier
        $mkts = YahooLibrary::getActiveMarkets();


        $identifiers = [];
        foreach($mkts as $mk) {
            $idf = new Identifier($mk, 'yahoo_daily_report_' . $mk);
            $identifiers[] = $idf;
        }
        return $identifiers;
    }
}
