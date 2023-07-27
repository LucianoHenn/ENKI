<?php

namespace App\Services\ARC\Sources\Providers\Taboola\Daily;

use Illuminate\Support\Facades\Storage;

use App\Exceptions\ARC\ReportException;
use App\Services\ARC\File\ReportUtils;
use App\Services\ARC\Sources\Abstracts\BaseDownloader;
use App\Models\ARC\ReportLogbook;
use Illuminate\Support\Facades\Log;
use App\Services\ARC\Sources\Providers\Taboola\TaboolaLibrary;

/**
 * Class TaboolaDailyDownloader
 */
class TaboolaDailyDownloader extends BaseDownloader
{
	public function doDownload(ReportLogbook $request): bool
	{
		try {
			$tClient = new TaboolaLibrary(config('arc.sources.taboola.client_id'), config('arc.sources.taboola.client_secret'));
			$tClient->setAccountId($request->identifier);

			$reportFile = ReportUtils::suggestOriginalLocalReportFullPath($request, true);
			$campaignData = $tClient->getCampaignSummaryReport($request->date_begin, $request->date_end);
			if ($campaignData !== null) {
				$jsonData = json_encode($campaignData);
				Storage::disk('system')->put($reportFile, $jsonData);
			}
		} catch (\Exception $e) {
			Log::warning('[TaboolaDailyDownloader][doDownload]:' . $e->getMessage());
			return false;
		}

		$request->infoOriginalLocalReport = $reportFile;
		return true;
	}
}
