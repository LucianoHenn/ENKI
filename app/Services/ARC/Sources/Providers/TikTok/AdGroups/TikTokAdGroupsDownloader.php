<?php

namespace App\Services\ARC\Sources\Providers\TikTok\AdGroups;

use Illuminate\Support\Facades\Storage;

use App\Exceptions\ARC\ReportException;
use App\Services\ARC\File\ReportUtils;
use App\Services\ARC\Sources\Abstracts\BaseDownloader;
use App\Models\ARC\ReportLogbook;
use Illuminate\Support\Facades\Log;
use App\Services\ARC\Sources\Providers\TikTok\TikTokLibrary;
/**
 * Class TikTokAdGroupsDownloader
 */
class TikTokAdGroupsDownloader extends BaseDownloader
{
    public function doDownload(ReportLogbook $request): bool
    {
        try {
            $lib = new TikTokLibrary();
            $lib->setAccessToken(config('arc.sources.tiktok.access_token'));

			$reportFile = ReportUtils::suggestOriginalLocalReportFullPath($request, true);
			$reportData = $lib->getAdGroups($request->identifier);
			if ($reportData !== null) {
				$jsonData = json_encode($reportData);
				Storage::disk('system')->put($reportFile, $jsonData);
			}
		} catch (\Exception $e) {
			Log::warning('[TikTokAdGroupsDownloader][doDownload]:' . $e->getMessage());
			throw new ReportException('[TikTokAdGroupsDownloader][doDownload]:' . $e->getMessage(), $request->source, $request->date_end);
			return false;
		}

		$request->infoOriginalLocalReport = $reportFile;
		return true;
    }
}
