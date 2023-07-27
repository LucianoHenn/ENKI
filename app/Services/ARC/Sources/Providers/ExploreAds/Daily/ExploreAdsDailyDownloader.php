<?php

namespace App\Services\ARC\Sources\Providers\ExploreAds\Daily;

use Illuminate\Support\Facades\Storage;

use App\Exceptions\ARC\ReportException;
use App\Services\ARC\File\ReportUtils;
use App\Services\ARC\Sources\Abstracts\BaseDownloader;
use App\Models\ARC\ReportLogbook;
use Illuminate\Support\Facades\Log;

use App\Services\ARC\Sources\Providers\ExploreAds\ExploreAdsLibrary;
/**
 * Class ExploreAdsDailyDownloader
 */
class ExploreAdsDailyDownloader extends BaseDownloader
{
    public function doDownload(ReportLogbook $request): bool
    {
        $library = new ExploreAdsLibrary();
        $reportFile = ReportUtils::suggestOriginalLocalReportFullPath($request, 'json');


        $data = $library->getReport('rs1', $request->date_begin, $request->date_end);

        if ($data === FALSE) {
            $errors = $library->getErrors();

            if (!empty($errors)) {
                $err = json_encode($errors);
                Log::warning('[ExploreAdsDailyDownloader] ' . $err);
                throw new ReportException($err, $request->source, $request->date_end);
            } else {
                return false;
            }
        }

        if(empty($data)) {
            $info = $request->info;
            if(isset($info['first_attempt'])) {
                if(time() - $info['first_attempt'] > 86400) {
                    $err = 'Attempts time expired with no data for ' . $request->date_end . ' we assume that the report is empty';
                    Log::warning('[ExploreAdsDailyDownloader] ' . $err);
                }
            } else {
                $info['first_attempt'] = time();
                return false;
            }
        }

        // Save File
        Storage::disk('system')->put($reportFile, json_encode($data));
        $request->infoOriginalLocalReport = $reportFile;
        return true;
    }
}
