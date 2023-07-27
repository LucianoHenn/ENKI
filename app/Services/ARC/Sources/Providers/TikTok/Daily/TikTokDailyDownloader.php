<?php

namespace App\Services\ARC\Sources\Providers\TikTok\Daily;

use Illuminate\Support\Facades\Storage;

use App\Exceptions\ARC\ReportException;
use App\Services\ARC\File\ReportUtils;
use App\Services\ARC\Sources\Abstracts\BaseDownloader;
use App\Models\ARC\ReportLogbook;
use Illuminate\Support\Facades\Log;
use App\Services\ARC\Sources\Providers\TikTok\TikTokLibrary;

/**
 * Class TikTokDailyDownloader
 */
class TikTokDailyDownloader extends BaseDownloader
{
	public function doDownload(ReportLogbook $request): bool
	{
		try {
			$lib = new TikTokLibrary();
			$lib->setAccessToken(config('arc.sources.tiktok.access_token'));

			$reportFile = ReportUtils::suggestOriginalLocalReportFullPath($request, true);
			$reportData = $lib->getSyncReport($request->identifier, $request->date_begin, $request->date_end);
			if ($reportData !== null) {

				if ($reportData->response->code != 0) {
					$err = $reportData->response->message ?? 'Unknown error';
					Log::warning($err);
					throw new ReportException($err, $request->source, $request->date_end);
					return false;
				} else {
					$jsonData = json_encode($reportData);
					Storage::disk('system')->put($reportFile, $jsonData);
				}
			}
		} catch (\Exception $e) {
			Log::warning('[TikTokDailyDownloader][doDownload]:' . $e->getMessage());
			throw new ReportException('[TikTokDailyDownloader][doDownload]:' . $e->getMessage(), $request->source, $request->date_end);
			return false;
		}

		$request->infoOriginalLocalReport = $reportFile;
		return true;
	}
}
