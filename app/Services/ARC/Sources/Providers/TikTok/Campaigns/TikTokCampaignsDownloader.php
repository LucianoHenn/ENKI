<?php

namespace App\Services\ARC\Sources\Providers\TikTok\Campaigns;

use Illuminate\Support\Facades\Storage;

use App\Exceptions\ARC\ReportException;
use App\Services\ARC\File\ReportUtils;
use App\Services\ARC\Sources\Abstracts\BaseDownloader;
use App\Models\ARC\ReportLogbook;
use Illuminate\Support\Facades\Log;
use App\Services\ARC\Sources\Providers\TikTok\TikTokLibrary;
/**
 * Class TikTokCampaignsDownloader
 */
class TikTokCampaignsDownloader extends BaseDownloader
{
    public function doDownload(ReportLogbook $request): bool
    {
        try {
            $lib = new TikTokLibrary();
            $lib->setAccessToken(config('arc.sources.tiktok.access_token'));

			$reportFile = ReportUtils::suggestOriginalLocalReportFullPath($request, true);
			$reportData = $lib->getCampaigns($request->identifier, $request->date_begin, $request->date_end);
			if ($reportData !== null) {
				$jsonData = json_encode($reportData);
				Storage::disk('system')->put($reportFile, $jsonData);
			}
		} catch (\Exception $e) {
			Log::warning('[TikTokCampaignsDownloader][doDownload]:' . $e->getMessage());
			throw new ReportException('[TikTokCampaignsDownloader][doDownload]:' . $e->getMessage(), $request->source, $request->date_end);
			return false;
		}

		$request->infoOriginalLocalReport = $reportFile;
		return true;
    }
}
