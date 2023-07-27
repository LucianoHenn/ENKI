<?php

namespace App\Services\ARC\Sources\Providers\Facebook\AdCreative;

use App\Services\ARC\Sources\Abstracts\BaseConfig;

/**
 * Class FacebookAdCreativeConfig
 */
class FacebookAdCreativeConfig extends BaseConfig
{
    // Whether the source can be used or not. Default: true
    public $enabled = true;

    // Family the report belongs to
    public $family = "Facebook";
    // Source name
    public $source = "Facebook";
    // Report type
    public $reportType = "AdCreative";

    // Enable or disable data export after import. Default: true
    public $exportProcessedData = true;
}
