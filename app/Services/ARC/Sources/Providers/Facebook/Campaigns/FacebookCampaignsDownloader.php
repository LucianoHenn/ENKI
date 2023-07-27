<?php

namespace App\Services\ARC\Sources\Providers\Facebook\Campaigns;

use Illuminate\Support\Facades\Storage;

use App\Exceptions\ARC\ReportException;
use App\Services\ARC\File\ReportUtils;
use App\Services\ARC\Sources\Abstracts\BaseDownloader;
use App\Models\ARC\ReportLogbook;
use Illuminate\Support\Facades\Log;

use App\Services\ARC\Sources\Providers\Facebook\FacebookLibrary;

/**
 * Class FacebookCampaignsDownloader
 */
class FacebookCampaignsDownloader extends BaseDownloader
{
    public function doDownload(ReportLogbook $request): bool
    {
        $identifier = $request->identifier;
        $gal = new FacebookLibrary();

        $reportFile = ReportUtils::suggestOriginalLocalReportFullPath($request, true, 'json');

        $response = $gal->getAccountCampaigns($identifier);
        if(empty($response)) {
            $err = $gal->getLastError();
            if(!empty($err)) {
                Log::warning('[FacebookCampaignsDownloader]: ' . json_encode($err));
                throw new ReportException($err->message, $request->source, $request->date_end);
            }
        } else {
            Storage::disk('system')->put($reportFile, json_encode($response));
            $request->infoOriginalLocalReport = $reportFile;
        }

        return true;
    }
}