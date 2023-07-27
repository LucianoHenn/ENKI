<?php

namespace App\Services\ARC\Sources\Providers\Taboola\Daily;

use App\Services\ARC\Sources\Abstracts\BaseConfig;

/**
 * Class TaboolaDailyConfig
 */
class TaboolaDailyConfig extends BaseConfig
{
    // Whether the source can be used or not. Default: true
    public $enabled = true;

    // Family the report belongs to
    public $family = "Taboola";
    // Source name
    public $source = "Taboola";
    // Report type
    public $reportType = "Daily";

    // Enable or disable data export after import. Default: true
    public $exportProcessedData = true;
}
