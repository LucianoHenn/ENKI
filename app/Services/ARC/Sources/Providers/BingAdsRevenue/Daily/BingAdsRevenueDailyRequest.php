<?php

namespace App\Services\ARC\Sources\Providers\BingAdsRevenue\Daily;

use App\Services\ARC\Sources\Abstracts\BaseRequest;
use App\Services\ARC\Elements\Identifier;

//if you need to use the arc associations table
use App\Models\ClientArcAssociation;


/**
 * Class BingAdsRevenueDailyRequest
 */
class BingAdsRevenueDailyRequest extends BaseRequest
{

    public function getIdentifiers() : array
    {
        $identifiers[] = new Identifier('all', 'bingadsrev-cbs');
        return $identifiers;
    }
}
