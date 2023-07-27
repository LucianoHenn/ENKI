<?php

namespace App\Services\ARC\Sources\Providers\System1\Subid;

use App\Services\ARC\Sources\Abstracts\BaseConfig;

/**
 * Class System1SubidConfig
 */
class System1SubidConfig extends BaseConfig
{
    // Whether the source can be used or not. Default: true
    public $enabled = true;

    // Family the report belongs to
    public $family = "System1";
    // Source name
    public $source = "System1";
    // Report type
    public $reportType = "Subid";

    // Enable or disable data export after import. Default: true
    public $exportProcessedData = true;
}
