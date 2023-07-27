<?php

namespace App\Services\ARC\Sources\Providers\AfsByCbs\Daily;

use App\Services\ARC\Sources\Abstracts\BaseConfig;

/**
 * Class AfsByCbsDailyConfig
 */
class AfsByCbsDailyConfig extends BaseConfig
{
    // Whether the source can be used or not. Default: true
    public $enabled = true;

    // Family the report belongs to
    public $family = "AfsByCbs";
    // Source name
    public $source = "AfsByCbs";
    // Report type
    public $reportType = "Daily";

    // Enable or disable data export after import. Default: true
    public $exportProcessedData = true;
}
