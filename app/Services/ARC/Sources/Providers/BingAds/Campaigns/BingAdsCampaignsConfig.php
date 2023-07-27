<?php

namespace App\Services\ARC\Sources\Providers\BingAds\Campaigns;

use App\Services\ARC\Sources\Abstracts\BaseConfig;

/**
 * Class BingAdsCampaignsConfig
 */
class BingAdsCampaignsConfig extends BaseConfig
{
    // Whether the source can be used or not. Default: true
    public $enabled = true;

    // Family the report belongs to
    public $family = "BingAds";
    // Source name
    public $source = "BingAds";
    // Report type
    public $reportType = "Campaigns";

    // Enable or disable data export after import. Default: true
    public $exportProcessedData = true;
}