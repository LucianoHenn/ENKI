<?php

namespace App\Services\ARC\Sources\Providers\Yahoo\Hourly;

use App\Services\ARC\Sources\Abstracts\BaseConfig;

/**
 * Class YahooHourlyConfig
 */
class YahooHourlyConfig extends BaseConfig
{
    // Whether the source can be used or not. Default: true
    public $enabled = true;

    // Family the report belongs to
    public $family = "Yahoo";
    // Source name
    public $source = "Yahoo";
    // Report type
    public $reportType = "Hourly";

    // Enable or disable data export after import. Default: true
    public $exportProcessedData = true;
}
