<?php

namespace App\Services\ARC\Sources\Providers\BingAds\Keywordperformance;

use App\Services\ARC\Sources\Abstracts\BaseRequest;
use App\Services\ARC\Elements\Identifier;

use App\Models\ClientArcAssociation;

/**
 * Class BingAdsKeywordperformanceRequest
 */
class BingAdsKeywordperformanceRequest extends BaseRequest
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
            $idf = new Identifier($assoc->market->code, $assoc->info['account_number']);
            $identifiers[] = $idf;
        }
        return $identifiers;
    }
}
