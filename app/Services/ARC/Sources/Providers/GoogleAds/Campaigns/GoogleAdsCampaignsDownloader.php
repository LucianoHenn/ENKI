<?php

namespace App\Services\ARC\Sources\Providers\GoogleAds\Campaigns;

use Illuminate\Support\Facades\Storage;

use App\Exceptions\ARC\ReportException;
use App\Services\ARC\File\ReportUtils;
use App\Services\ARC\Sources\Abstracts\BaseDownloader;
use App\Models\ARC\ReportLogbook;
use Illuminate\Support\Facades\Log;

use App\Services\ARC\Sources\Providers\GoogleAds\GoogleAdsLibrary;


/**
 * Class GoogleAdsCampaignsDownloader
 */
class GoogleAdsCampaignsDownloader extends BaseDownloader
{
    public function doDownload(ReportLogbook $request): bool
    {
        $identifier = $request->identifier;
        $gal = new GoogleAdsLibrary();

        if (!$gal->isAuthenticated()) {
            Log::error('GoogleAdsCampaignsDownloader] - Authentication error');
            return false;
        }
        $reportFile = ReportUtils::suggestOriginalLocalReportFullPath($request, true, 'json');

        if(!$gal->getCustomerIdCampaigns($identifier)) {
            $err = json_encode($gal->getErrors());
            Log::error('[GoogleAdsCampaignsDownloader] ' . $err);
            throw new ReportException($err, $request->source, $request->date_end);
        } else {
            Storage::disk('system')->put($reportFile, json_encode($gal->getData()));
            $request->infoOriginalLocalReport = $reportFile;
        }

        return true;
    }
}
