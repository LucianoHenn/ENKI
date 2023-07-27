<?php

namespace App\Services\ARC\Sources\Providers\BingAdsRevenue\Daily;

use App\Services\ARC\Sources\Abstracts\BaseConfig;

/**
 * Class BingAdsRevenueDailyConfig
 */
class BingAdsRevenueDailyConfig extends BaseConfig
{
    // Whether the source can be used or not. Default: true
    public $enabled = true;

    // Family the report belongs to
    public $family = "BingAdsRevenue";
    // Source name
    public $source = "BingAdsRevenue";
    // Report type
    public $reportType = "Daily";

    // Enable or disable data export after import. Default: true
    public $exportProcessedData = true;
}
