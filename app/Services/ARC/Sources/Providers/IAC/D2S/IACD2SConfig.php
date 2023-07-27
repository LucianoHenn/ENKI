<?php

namespace App\Services\ARC\Sources\Providers\IAC\D2S;

use App\Services\ARC\Sources\Abstracts\BaseConfig;

/**
 * Class IACD2SConfig
 */
class IACD2SConfig extends BaseConfig
{
    // Whether the source can be used or not. Default: true
    public $enabled = true;

    // Family the report belongs to
    public $family = "IAC";
    // Source name
    public $source = "IAC";
    // Report type
    public $reportType = "D2S";

    // Enable or disable data export after import. Default: true
    public $exportProcessedData = true;
}
