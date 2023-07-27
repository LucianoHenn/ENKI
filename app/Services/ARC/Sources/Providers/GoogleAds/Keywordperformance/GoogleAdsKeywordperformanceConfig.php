<?php

namespace App\Services\ARC\Sources\Providers\GoogleAds\Keywordperformance;

use App\Services\ARC\Sources\Abstracts\BaseConfig;

/**
 * Class GoogleAdsKeywordperformanceConfig
 */
class GoogleAdsKeywordperformanceConfig extends BaseConfig
{
    // Whether the source can be used or not. Default: true
    public $enabled = true;

    // Family the report belongs to
    public $family = "GoogleAds";
    // Source name
    public $source = "GoogleAds";
    // Report type
    public $reportType = "Keywordperformance";

    // Enable or disable data export after import. Default: true
    public $exportProcessedData = true;
}
