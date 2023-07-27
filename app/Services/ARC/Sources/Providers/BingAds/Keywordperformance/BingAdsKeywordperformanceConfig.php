<?php

namespace App\Services\ARC\Sources\Providers\BingAds\Keywordperformance;

use App\Services\ARC\Sources\Abstracts\BaseConfig;

/**
 * Class BingAdsKeywordperformanceConfig
 */
class BingAdsKeywordperformanceConfig extends BaseConfig
{
    // Whether the source can be used or not. Default: true
    public $enabled = true;

    // Family the report belongs to
    public $family = "BingAds";
    // Source name
    public $source = "BingAds";
    // Report type
    public $reportType = "Keywordperformance";

    // Enable or disable data export after import. Default: true
    public $exportProcessedData = true;
}
