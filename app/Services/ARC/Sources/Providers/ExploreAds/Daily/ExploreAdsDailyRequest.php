<?php

namespace App\Services\ARC\Sources\Providers\ExploreAds\Daily;

use App\Services\ARC\Sources\Abstracts\BaseRequest;
use App\Services\ARC\Elements\Identifier;

//if you need to use the arc associations table
use App\Models\ClientArcAssociation;


/**
 * Class ExploreAdsDailyRequest
 */
class ExploreAdsDailyRequest extends BaseRequest
{
    protected $identifier_field = 'ad_client_id';

    public function getIdentifiers() : array
    {
        $validAssociations = ClientArcAssociation::active()
            ->where('source', $this->source)
            ->inPeriod($this->reportDate)
            ->get();

        $identifiers = [];
        foreach ($validAssociations as $assoc) {
            $identifier = $assoc->info[$this->identifier_field] . '__' . $assoc->info['channels_prefix'];
            $idf = new Identifier($assoc->market->code, $identifier);
            $identifiers[] = $idf;
        }
		  return $identifiers;
    }
}
