<?php

namespace App\Services\ARC\Sources\Providers\Yahoo\Daily;

use App\Services\ARC\Sources\Abstracts\BaseConfig;

/**
 * Class YahooDailyConfig
 */
class YahooDailyConfig extends BaseConfig
{
    // Whether the source can be used or not. Default: true
    public $enabled = true;

    // Family the report belongs to
    public $family = "Yahoo";
    // Source name
    public $source = "Yahoo";
    // Report type
    public $reportType = "Daily";

    // Enable or disable data export after import. Default: true
    public $exportProcessedData = true;
}
