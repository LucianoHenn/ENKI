<?php

namespace App\Services\ARC\Sources\Providers\Zemanta\Daily;

use Illuminate\Support\Facades\Storage;

use App\Exceptions\ARC\ReportException;
use App\Services\ARC\File\ReportUtils;
use App\Services\ARC\Sources\Abstracts\BaseDownloader;
use App\Models\ARC\ReportLogbook;
use Illuminate\Support\Facades\Log;

use App\Services\ARC\Sources\Providers\Zemanta\ZemantaLibrary;
use League\Csv\Reader;


/**
 * Class ZemantaDailyDownloader
 */
class ZemantaDailyDownloader extends BaseDownloader
{
    public function doDownload(ReportLogbook $request): bool
    {
        try {

            $account_id = $request->identifier;
            $lib = new ZemantaLibrary();

            $reportFile = ReportUtils::suggestOriginalLocalReportFullPath($request, true, 'json');

            if (!isset($request->info['job_ids'])) {
                // request
                Log::debug("[ZemantaDailyDownloader] Request new report to Zemanta");
                $info = $request->info;
                $info['job_ids'] = [];
                $info['job_ids']['view'] = $lib->requestReport($account_id, $request->date_end, $request->date_end);
                $info['job_ids']['click'] = $lib->requestReport($account_id, $request->date_end, $request->date_end, 'click');
                $request->info = $info;
                
                return false;
            } else {

                $dataResults = [];
                foreach ($request->info['job_ids'] as $job_type => $job_id) {
                    // download
                    Log::debug("[ZemantaDailyDownloader] Try to download report from jobId = {$job_type}/{$job_id}");
                    $dataResults[ $job_type ] = $lib->downloadReport($account_id, $job_id);
                    if ($dataResults[ $job_type ] === false) {
                        throw new ReportException('Empty Response', $request->source, $request->date_end);
                        return false;
                    }
                    if (in_array($dataResults[ $job_type ]->data->status, ['IN_PROGRESS'])) {
                        Log::info("[ZemantaDailyDownloader] Waiting for job completition on {$account_id} , {$job_type}/{$job_id}");
                        return false;
                    }
                    if (in_array($dataResults[ $job_type ]->data->status, ['FAILED', 'CANCELLED'])) {
                        Log::info("[ZemantaDailyDownloader] Job {$job_id} failed/cancelled on {$account_id}: " . $dataResults[ $job_type ]->data->result);
                        $info = $request->info;
                        unset($info['job_ids']);
                        $request->info = $info;
                        return false;
                    }
                    if (empty($dataResults[ $job_type ]->data)) {
                        Log::info("[ZemantaDailyDownloader] Empty data on {$account_id} , {$job_id}");
                        $info = $request->info;
                        unset($info['job_ids']);
                        $request->info = $info;
                        return false;
                    }
                    if (empty($dataResults[ $job_type ]->data->content)) {
                        Log::info("[ZemantaDailyDownloader] Empty data content on {$account_id} , {$job_id}");
                        $info = $request->info;
                        unset($info['job_ids']);
                        $request->info = $info;
                        return false;
                    }
                }

                $reader_view = Reader::createFromString($dataResults[ 'view' ]->data->content);
                $reader_view->setHeaderOffset(0);
                $reader_click = Reader::createFromString($dataResults[ 'click' ]->data->content);
                $reader_click->setHeaderOffset(0);

                $maps = [];
                foreach($reader_click as $row) {
                    //dd($row);
                    $id = $row['Campaign Id'];
                    $maps[$id] = $row['conv - Click attr.'] ?? 0;
                }

                $data = [];

                foreach($reader_view as $row) {
                    $id = $row['Campaign Id'];
                    $row['conv - Click attr.'] = $maps[$id] ?? 0;
                    $data[] = $row;
                }

                // Save File
                
                Log::debug("[ZemantaDailyDownloader] Saving report content");
                Storage::disk('system')->put($reportFile, json_encode($data));
                $request->infoOriginalLocalReport = $reportFile;
                return true;
            }

        } catch (\Exception $e) {

            if(stripos($e->getMessage(), 'request was throttled') !== FALSE) {
                Log::warning('[ZemantaDailyDownloader] ' . $e->getMessage());

                return false;
            }
            Log::error('[ZemantaDailyDownloader] ' . $e->getMessage());

            throw $e;
        }
        return true;
    }
}
