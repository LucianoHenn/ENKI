<?php

namespace App\Services\ARC\Sources\Abstracts;

use App\Services\ARC\Sources\Abstracts\BaseConfig;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

class BasePhase
{
    protected $source;
    protected $reportType;
    protected $reportDate;
    protected $force = false;

    protected $sourceConfig = null;

    public function __construct(string $source, string $reportType, string $reportDate, bool $force = false)
    {
        $this->source = $source;
        $this->reportType = $reportType;
        $this->reportDate = $reportDate;
        $this->force = $force;

        $this->init();
    }

    public function setSourceConfig(BaseConfig $cfg)
    {
        $this->sourceConfig = $cfg;
    }

    public function getSourceConfig()
    {
        return $this->sourceConfig;
    }

    //can be overridden
    public function init()
    {
    }
}
