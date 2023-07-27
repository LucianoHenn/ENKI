<?php

namespace App\Services\ARC\Sources\Abstracts;

abstract class BaseConfig
{
    protected $enabled = true;
    protected $exportProcessedData = true;
    protected $source = null;
    protected $reportType = "Daily";
    protected $family = null;

    public function isEnabled()
    {
        return $this->enabled;
    }

    public function canExportProcessedData()
    {
        return $this->exportProcessedData;
    }


    public function getSource()
    {
        return $this->source;
    }

    public function getReportType()
    {
        return $this->reportType;
    }

    public function getReportFamily()
    {
        return $this->family;
    }
}
