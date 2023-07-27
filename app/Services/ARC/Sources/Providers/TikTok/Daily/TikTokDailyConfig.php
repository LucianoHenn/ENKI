<?php

namespace App\Services\ARC\Sources\Providers\TikTok\Daily;

use App\Services\ARC\Sources\Abstracts\BaseConfig;

/**
 * Class TikTokDailyConfig
 */
class TikTokDailyConfig extends BaseConfig
{
    // Whether the source can be used or not. Default: true
    public $enabled = true;

    // Family the report belongs to
    public $family = "TikTok";
    // Source name
    public $source = "TikTok";
    // Report type
    public $reportType = "Daily";

    // Enable or disable data export after import. Default: true
    public $exportProcessedData = true;
}
