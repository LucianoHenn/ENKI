<?php

namespace App\Services\ARC\Sources\Providers\AfsByCbs\Daily;

use App\Services\ARC\Sources\Abstracts\BaseRequest;
use App\Services\ARC\Elements\Identifier;

//if you need to use the arc associations table
use App\Models\ClientArcAssociation;


/**
 * Class AfsByCbsDailyRequest
 */
class AfsByCbsDailyRequest extends BaseRequest
{
    public function getIdentifiers() : array
    {
        // Return the list of identifier
        $validAssociations = ClientArcAssociation::active()
            ->where('source', $this->source)
            ->inPeriod($this->reportDate)
            ->get();

        $identifiers = [];
        foreach ($validAssociations as $assoc) {
            $idf = new Identifier($assoc->market->code, $assoc->info['site_id']);
            $identifiers[] = $idf;
        }
        return $identifiers;
    }
}
