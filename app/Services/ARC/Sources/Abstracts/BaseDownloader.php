<?php

namespace App\Services\ARC\Sources\Abstracts;

use App\Services\ARC\File\ReportUtils;
use App\Services\ARC\Sources\Abstracts\BasePhase;
use App\Models\ARC\ReportLogbook;

abstract class BaseDownloader extends BasePhase
{
    abstract public function doDownload(ReportLogbook $request) : bool;

    public function copyOriginalLocalReportToS3(ReportLogbook $request) : bool
    {
        return ReportUtils::copyOriginalLocalReportToS3($request);
    }

    public function copyProcessedLocalReportToS3($processedLocalReport, ReportLogbook $request, $date = null) : bool
    {
        return ReportUtils::copyProcessedLocalReportToS3($processedLocalReport, $request, $date);
    }
}


