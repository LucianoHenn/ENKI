<?php

namespace App\Services\ARC\Sources\Providers\BingAds\Campaigns;

use Illuminate\Support\Facades\Storage;

use App\Exceptions\ARC\ReportException;
use App\Services\ARC\File\ReportUtils;
use App\Services\ARC\Sources\Abstracts\BaseDownloader;
use App\Models\ARC\ReportLogbook;
use Illuminate\Support\Facades\Log;
use App\Services\ARC\Sources\Providers\BingAds\BingAdsLibrary;
use Illuminate\Support\Facades\Cache;

/**
 * Class BingAdsCampaignsDownloader
 */
class BingAdsCampaignsDownloader extends BaseDownloader
{
    protected $cacheDuration = 3600; //1 hour cache

    public function doDownload(ReportLogbook $request): bool
    {
        $identifier = $request->identifier;
        $bal = new BingAdsLibrary();

        /*-- Authenticate credentials from .env --*/
        if (!$bal->isAuthenticated()) {
            Log::error('BingAdsCampaignsDownloader] - Authentication error');
            return false;
        }

        /*-- Get all BingAds accounts available --*/
        Log::info('[BingAdsCampaignsDownloader] - Getting all accounts from BingAds');

        $accounts = Cache::get('bingads-cost-account-lists');

        if(empty($accounts)) {
            $accounts = $bal->getAllAccounts();
            Cache::put('bingads-cost-account-lists', $accounts, $this->cacheDuration);
        }


        if (empty($accounts)) {
            Log::error('[BingAdsCampaignsDownloader] - Empty accounts');
            return false;
        }
        $isAccountActive = false;
        $currentAccount = null;
        foreach ($accounts as $account) {
            /*-- Check if Account exists/are active from site_associations --*/
            if ($account->number == $identifier) {
                $isAccountActive = ($account->status == 'Active');
                $currentAccount = $account;
                break;
            }
        }

        if (!$isAccountActive) {
            $err = 'Account ' . $identifier . ' not active';
            Log::error('[BingAdsCampaignsDownloader] ' . $err);
            throw new ReportException($err, $request->source, $request->date_end);
        }

        Log::info('[BingAdsCampaignsDownloader] - Downloading Account: ' . json_encode($account));
        $reportFile = ReportUtils::suggestOriginalLocalReportFullPath($request, true, 'json');


        try {
            $reportData = $bal->getCampaigns($account->id);
            if (empty($reportData)) {

                $error = $bal->getError();
                if(!empty($error)) { 
                    Log::warning('[BingAdsCampaignsDownloader][getcCampaigns]: ' . json_encode($error));
                    return false;
                }
                else {
                    return true;
                }
            } else {
                file_put_contents($reportFile, json_encode($reportData));
                $request->infoOriginalLocalReport = $reportFile;
            }
        } catch (\Exception $e) {
            $err = (string) "[BingAdsCampaignsDownloader] Unable to download data for: " . json_encode($request);
            throw new ReportException($err, $request->source, $request->date_end);
        }

        return true;
    }
}
