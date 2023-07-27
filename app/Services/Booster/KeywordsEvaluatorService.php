<?php

namespace App\Services\Booster;


use App\Models\Booster\Keyword as Keyword;
use App\Models\Booster\RelatedKeyword as RelatedKeyword;


use App\Services\Booster\RevenuePartners\YadsRevenuePartnerService;


use Illuminate\Database\Eloquent\Collection;
use Exception;

class KeywordsEvaluatorService
{

    private $revenuePartner;
    private $revenuePartnerService;
    private $relatedKeywords;
    private $keyword;

    public function __construct(Keyword $keyword, Collection $relatedKeywords)
    {
        $this->revenuePartner   = $keyword->revenue_partner;
        $this->relatedKeywords  = $relatedKeywords;

        switch ($this->revenuePartner) {


            case 'yads':
                $this->revenuePartnerService = new YadsRevenuePartnerService($keyword, $relatedKeywords);
                break;

            default:
                throw (new Exception("Invalid partner: " . $this->revenuePartner));
                break;
        }
    }

    public function processKwds(): void
    {
        $this->revenuePartnerService->retrieveEconomicalValues(
            $this->keyword,
            $this->relatedKeywords
        );
    }

    public function getKwdMetrics(string $keyword): array
    {
        return $this->revenuePartnerService->getKwdMetrics($keyword);
    }
}