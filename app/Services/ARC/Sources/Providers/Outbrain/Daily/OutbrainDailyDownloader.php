<?php

namespace App\Services\ARC\Sources\Providers\Outbrain\Daily;

use Illuminate\Support\Facades\Storage;

use App\Exceptions\ARC\ReportException;
use App\Services\ARC\File\ReportUtils;
use App\Services\ARC\Sources\Abstracts\BaseDownloader;
use App\Models\ARC\ReportLogbook;
use Illuminate\Support\Facades\Log;

use App\Services\ARC\Sources\Providers\Outbrain\OutbrainLibrary;

/**
 * Class OutbrainDailyDownloader
 */
class OutbrainDailyDownloader extends BaseDownloader
{
    public function doDownload(ReportLogbook $request): bool
    {
        $account_id = $request->identifier;
        $out = new OutbrainLibrary();

        $reportFile = ReportUtils::suggestOriginalLocalReportFullPath($request);
        $success = $out->requestPerformanceReport($account_id, $request->date_end, $request->date_end);
        if ($success) {
            // Save File
            Storage::disk('system')->put($reportFile, json_encode($out->getResponse()));
            $request->infoOriginalLocalReport = $reportFile;
        } else {
            $err = $out->getError();
            throw new ReportException($err, $request->source, $request->date_end);
        }

        return true;
    }
}
