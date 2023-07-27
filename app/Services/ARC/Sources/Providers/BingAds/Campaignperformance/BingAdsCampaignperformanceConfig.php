<?php

namespace App\Services\ARC\Sources\Providers\BingAds\Campaignperformance;

use App\Services\ARC\Sources\Abstracts\BaseConfig;

/**
 * Class BingAdsCampaignperformanceConfig
 */
class BingAdsCampaignperformanceConfig extends BaseConfig
{
    // Whether the source can be used or not. Default: true
    public $enabled = true;

    // Family the report belongs to
    public $family = "BingAds";
    // Source name
    public $source = "BingAds";
    // Report type
    public $reportType = "Campaignperformance";

    // Enable or disable data export after import. Default: true
    public $exportProcessedData = true;
}
