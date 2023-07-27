<?php

namespace App\Services\ARC\Sources\Providers\AfsByCbs\Daily;

use Illuminate\Support\Facades\Storage;

use App\Exceptions\ARC\ReportException;
use App\Services\ARC\File\ReportUtils;
use App\Services\ARC\Sources\Abstracts\BaseDownloader;
use App\Models\ARC\ReportLogbook;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

use App\Services\ARC\Sources\Providers\AfsByCbs\AfsByCbsLibrary;

/**
 * Class AfsByCbsDailyDownloader
 */
class AfsByCbsDailyDownloader extends BaseDownloader
{
    public function doDownload(ReportLogbook $request): bool
    {
        $reportFile = ReportUtils::suggestOriginalLocalReportFullPath($request, true, 'csv');
        $lib = new AfsByCbsLibrary($request->identifier, config('arc.sources.afsbycbs.secret_key'));

        $date_begin = Carbon::createFromFormat('Y-m-d', $request->date_end)->subDays(10)->format('Y-m-d');
        $request->date_begin = $date_begin;
        $date_end = $request->date_end;
        if(!$lib->downloadReport($date_begin, $date_end, $reportFile)) {
            $error = $lib->getLastError();
            Log::warning('[AfsByCbsDailyDownloader][doDownload]:' . json_encode($error));
            return false;
        }
        $request->infoOriginalLocalReport = $reportFile;
        return true;
    }
}
