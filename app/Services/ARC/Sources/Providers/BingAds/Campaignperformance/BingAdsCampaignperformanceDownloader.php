<?php

namespace App\Services\ARC\Sources\Providers\BingAds\Campaignperformance;

use Illuminate\Support\Facades\Storage;

use App\Exceptions\ARC\ReportException;
use App\Services\ARC\File\ReportUtils;
use App\Services\ARC\Sources\Abstracts\BaseDownloader;
use App\Models\ARC\ReportLogbook;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

use App\Services\ARC\Sources\Providers\BingAds\BingAdsLibrary;


/**
 * Class BingAdsCampaignperformanceDownloader
 */
class BingAdsCampaignperformanceDownloader extends BaseDownloader
{

    protected $cacheDuration = 3600; //1 hour cache


    public function doDownload(ReportLogbook $request): bool
    {
        $identifier = $request->identifier;
        $bal = new BingAdsLibrary();

        /*-- Authenticate credentials from .env --*/
        if (!$bal->isAuthenticated()) {
            Log::error('BingAdsCampaignperformanceDownloader] - Authentication error');
            return false;
        }

        /*-- Get all BingAds accounts available --*/
        Log::info('[BingAdsCampaignperformanceDownloader] - Getting all accounts from BingAds');

        $accounts = Cache::get('bingads-cost-account-lists');

        if(empty($accounts)) {
            $accounts = $bal->getAllAccounts();
            Cache::put('bingads-cost-account-lists', $accounts, $this->cacheDuration);
        }


        if (empty($accounts)) {
            Log::error('[BingAdsCampaignperformanceDownloader] - Empty accounts');
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
            Log::error('[BingAdsCampaignperformanceDownloader] ' . $err);
            throw new ReportException($err, $request->source, $request->date_end);
        }

        Log::info('[BingAdsCampaignperformanceDownloader] - Downloading Account: ' . json_encode($account));
        $reportFile = ReportUtils::suggestOriginalLocalReportFullPath($request, true, 'csv');


        try {
            if (!$bal->getCampaignPerformanceReport(array($account->id), $request->date_end, $reportFile)) {

                $error = json_encode($bal->getError());
                Log::warning('[BingAdsCampaignperformanceDownloader][getCampaignPerformanceReport]: ' . $error);

                return false;
            } else {
                $request->infoOriginalLocalReport = $reportFile;
            }
        } catch (\Exception $e) {
            $err = (string) "[BingAdsCampaignperformanceDownloader] Unable to download data for: " . json_encode($request);
            throw new ReportException($err, $request->source, $request->date_end);
        }

        return true;
    }
}
