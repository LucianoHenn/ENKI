<?php

namespace App\Services\ARC\Sources\Providers\Outbrain\Daily;

use App\Services\ARC\Sources\Abstracts\BaseConfig;

/**
 * Class OutbrainDailyConfig
 */
class OutbrainDailyConfig extends BaseConfig
{
    // Whether the source can be used or not. Default: true
    public $enabled = true;

    // Family the report belongs to
    public $family = "Outbrain";
    // Source name
    public $source = "Outbrain";
    // Report type
    public $reportType = "Daily";

    // Enable or disable data export after import. Default: true
    public $exportProcessedData = true;
}
