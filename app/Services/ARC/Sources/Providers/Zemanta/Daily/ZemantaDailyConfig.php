<?php

namespace App\Services\ARC\Sources\Providers\Zemanta\Daily;

use App\Services\ARC\Sources\Abstracts\BaseConfig;

/**
 * Class ZemantaDailyConfig
 */
class ZemantaDailyConfig extends BaseConfig
{
    // Whether the source can be used or not. Default: true
    public $enabled = true;

    // Family the report belongs to
    public $family = "Zemanta";
    // Source name
    public $source = "Zemanta";
    // Report type
    public $reportType = "Daily";

    // Enable or disable data export after import. Default: true
    public $exportProcessedData = true;
}
