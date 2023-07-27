<?php

namespace App\Services\ARC\Sources\Providers\BingAdsRevenue\Daily;

use Illuminate\Support\Facades\Storage;

use App\Exceptions\ARC\ReportException;
use App\Services\ARC\File\ReportUtils;
use App\Services\ARC\Sources\Abstracts\BaseDownloader;
use App\Models\ARC\ReportLogbook;
use Illuminate\Support\Facades\Log;

use App\Services\ARC\Sources\Providers\BingAdsRevenue\BingAdsRevenueLibrary;

/**
 * Class BingAdsRevenueDailyDownloader
 */
class BingAdsRevenueDailyDownloader extends BaseDownloader
{
    public function doDownload(ReportLogbook $request): bool
    {

        $lib = new BingAdsRevenueLibrary();

        $reportFile = ReportUtils::suggestOriginalLocalReportFullPath($request, true);
        $dataResults = $lib->downloadData($request->date_end);

        if (isset($dataResults['status']) && $dataResults['status'] == false) {
            if(!empty($dataResults['error'])) {
                Log::warning('[BingAdsRevenueDailyDownloader][ERROR] '. $dataResults['error']);
            }
            return false;
        } else {
            // Save File
            $json = json_decode($dataResults);


            $maxDate = '1970-01-01';
            $minDate = '2100-01-01';
            foreach($json as $el) {
                if(isset($el->AdUnitId)) {
                    if(str_replace('-', '', $el->Date) > str_replace('-', '', $maxDate)) {
                        $maxDate = $el->Date;
                    }
                    if(str_replace('-', '', $el->Date) < str_replace('-', '', $minDate)) {
                        $minDate = $el->Date;
                    }

                }
            }
            $request->date_end = $maxDate;
            $request->date_begin = $minDate;


            Storage::disk('system')->put($reportFile, $dataResults);
            $request->infoOriginalLocalReport = $reportFile;
        }
        return true;
    }
}
