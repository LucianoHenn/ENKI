<?php

namespace App\Services\ARC\Sources\Providers\Tonic\Daily;

use App\Services\ARC\Sources\Abstracts\BaseConfig;

/**
 * Class TonicDailyConfig
 */
class TonicDailyConfig extends BaseConfig
{
    // Whether the source can be used or not. Default: true
    public $enabled = true;

    // Family the report belongs to
    public $family = "Tonic";
    // Source name
    public $source = "Tonic";
    // Report type
    public $reportType = "Daily";

    // Enable or disable data export after import. Default: true
    public $exportProcessedData = true;
}
