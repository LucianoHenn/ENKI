<?php

namespace App\Services\ARC\Sources\Providers\GoogleAds\Campaigns;

use App\Services\ARC\Sources\Abstracts\BaseRequest;
use App\Services\ARC\Elements\Identifier;

//if you need to use the arc associations table
use App\Models\ClientArcAssociation;


/**
 * Class GoogleAdsCampaignsRequest
 */
class GoogleAdsCampaignsRequest extends BaseRequest
{
    public function getIdentifiers() : array
    {
        $validAssociations = ClientArcAssociation::active()
            ->where('source', $this->source)
            ->inPeriod($this->reportDate)
            ->get();

        $identifiers = [];
        foreach ($validAssociations as $assoc) {
            $idf = new Identifier($assoc->market->code, $assoc->info['customer_id']);
            $identifiers[] = $idf;
        }
        return $identifiers;
    }
}
