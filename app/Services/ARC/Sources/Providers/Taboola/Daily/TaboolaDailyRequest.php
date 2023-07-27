<?php

namespace App\Services\ARC\Sources\Providers\Taboola\Daily;

use App\Services\ARC\Sources\Abstracts\BaseRequest;
use App\Services\ARC\Elements\Identifier;

//if you need to use the arc associations table
use App\Models\ClientArcAssociation;

use App\Services\ARC\Sources\Providers\Taboola\TaboolaLibrary;


/**
 * Class TaboolaDailyRequest
 */
class TaboolaDailyRequest extends BaseRequest
{
    public function getIdentifiers() : array
    {
        $validAssociations = ClientArcAssociation::active()
            ->where('source', $this->source)
            ->inPeriod($this->reportDate)
            ->get();

        $identifiers = [];
        foreach ($validAssociations as $assoc) {
            $idf = new Identifier($assoc->market->code, $assoc->info['account_id']);
            $identifiers[] = $idf;
        }
		  return $identifiers;
    }
}
