<?php

namespace App\Services\ARC\Sources\Providers\Zemanta\Campaigns;

use Illuminate\Support\Facades\Storage;

use App\Exceptions\ARC\ReportException;
use App\Services\ARC\File\ReportUtils;
use App\Services\ARC\Sources\Abstracts\BaseDownloader;
use App\Models\ARC\ReportLogbook;
use Illuminate\Support\Facades\Log;
use App\Services\ARC\Sources\Providers\Zemanta\ZemantaLibrary;

/**
 * Class ZemantaCampaignsDownloader
 */
class ZemantaCampaignsDownloader extends BaseDownloader
{
    public function doDownload(ReportLogbook $request): bool
    {
        $account_id = $request->identifier;
        $lib = new ZemantaLibrary();

        $reportFile = ReportUtils::suggestOriginalLocalReportFullPath($request);
        try {

            $dataResults = $lib->getCampaigns($account_id);
            // Save File
            Log::debug("[ZemantaDailyDownloader] Saving campaigns content for: " .$account_id );
            Storage::disk('system')->put($reportFile, json_encode($dataResults));
            $request->infoOriginalLocalReport = $reportFile;
            return true;
        } catch (\Exception $e) {

            if (stripos($e->getMessage(), 'request was throttled') !== FALSE) {
                Log::warning('[ZemantaCampaignsDownloader] ' . $e->getMessage());

                return false;
            }
            Log::error('[ZemantaCampaignsDownloader] ' . $e->getMessage());

            throw $e;
        }
        return true;
    }
}
