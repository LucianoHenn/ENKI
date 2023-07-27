<?php

namespace App\Services\ARC\Sources\Providers\Tonic\Daily;

use App\Services\ARC\Sources\Abstracts\BaseRequest;
use App\Services\ARC\Elements\Identifier;

//if you need to use the arc associations table
use App\Models\ClientArcAssociation;


/**
 * Class TonicDailyRequest
 */
class TonicDailyRequest extends BaseRequest
{
    protected $identifier_field = 'key';

    public function getIdentifiers() : array
    {
        $validAssociations = ClientArcAssociation::active()
            ->where('source', $this->source)
            ->inPeriod($this->reportDate)
            ->get();

        $identifiers = [];
        foreach ($validAssociations as $assoc) {
            $idf = new Identifier($assoc->market->code, $assoc->info[$this->identifier_field]);
            $identifiers[] = $idf;
        }
		  return $identifiers;
    }
}
