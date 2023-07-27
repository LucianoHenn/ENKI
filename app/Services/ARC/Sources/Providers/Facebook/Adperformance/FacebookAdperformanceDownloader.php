<?php

namespace App\Services\ARC\Sources\Providers\Facebook\Adperformance;

use Illuminate\Support\Facades\Storage;

use App\Exceptions\ARC\ReportException;
use App\Services\ARC\File\ReportUtils;
use App\Services\ARC\Sources\Abstracts\BaseDownloader;
use App\Models\ARC\ReportLogbook;
use Illuminate\Support\Facades\Log;
use App\Services\ARC\Sources\Providers\Facebook\FacebookLibrary;
/**
 * Class FacebookAdperformanceDownloader
 */
class FacebookAdperformanceDownloader extends BaseDownloader
{
    public function doDownload(ReportLogbook $request): bool
    {
        $account_id = $request->identifier;
        $fbl = new FacebookLibrary();

        $reportFile = ReportUtils::suggestOriginalLocalReportFullPath($request, 'json');

        $info = $request->info;
        $jobId = $info['job_id'] ?? null;

        if (empty($jobId)) {
            Log::info("[FacebookAdperformanceDownloader] Request new report to Facebook");
            $jobId = $fbl->requestReport($account_id, $request->date_end, $request->date_end);

            if(empty($jobId)) {
                $err = (string) $fbl->getLastError();
                throw new ReportException($err, $request->source, $request->date_end);
            }
            Log::info("[FacebookAdperformanceDownloader] Requested new report to Facebook, jobId = {$jobId}");
            $info['job_id'] = $jobId;
            $request->info = $info;
            return false;
        } else {
            Log::info("[FacebookAdperformanceDownloader] Try to download report from jobId = {$jobId}");
            $dataResults = $fbl->downloadReport($account_id, $jobId, true);
            if ($dataResults === false) {
                $err = (string) $fbl->getLastError();
                throw new ReportException($err, $request->source, $request->date_end);
            } else {
                if (isset($dataResults['data'])) {
                    // Save File
                    if($dataResults['status'] == 'exception') {
                        throw new ReportException($dataResults['status'], $request->source, $request->date_end);
                        return false;
                    }
                    Storage::disk('system')->put($reportFile, json_encode($dataResults));
                    $request->infoOriginalLocalReport = $reportFile;
                    return true;
                } else {
                    if (in_array($dataResults['status'], ['job running', 'job started', 'job not started'])) {
                        Log::info("[FacebookAdperformanceDownloader] Waiting for job completition on {$account_id} , {$jobId}");
                        return false;
                    } else {
                        $info = $request->info;
                        unset($info['job_id']);
                        $request->info = $info; //to request a new job
                        return false;
                    }
                }
            }
        }
        return true;
    }
}
