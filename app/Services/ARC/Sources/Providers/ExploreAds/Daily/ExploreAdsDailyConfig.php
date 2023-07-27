<?php

namespace App\Services\ARC\Sources\Providers\ExploreAds\Daily;

use App\Services\ARC\Sources\Abstracts\BaseConfig;

/**
 * Class ExploreAdsDailyConfig
 */
class ExploreAdsDailyConfig extends BaseConfig
{
    // Whether the source can be used or not. Default: true
    public $enabled = true;

    // Family the report belongs to
    public $family = "ExploreAds";
    // Source name
    public $source = "ExploreAds";
    // Report type
    public $reportType = "Daily";

    // Enable or disable data export after import. Default: true
    public $exportProcessedData = true;
}
