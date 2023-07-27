<?php

namespace App\Services\ARC\Sources\Providers\Yahoo\Daily;

use Illuminate\Support\Facades\Storage;

use App\Exceptions\ARC\ReportException;
use App\Services\ARC\File\ReportUtils;
use App\Services\ARC\Sources\Abstracts\BaseDownloader;
use App\Models\ARC\ReportLogbook;
use Illuminate\Support\Facades\Log;

use App\Services\ARC\Sources\Providers\Yahoo\YahooLibrary;
use Carbon\Carbon;

/**
 * Class YahooDailyDownloader
 */
class YahooDailyDownloader extends BaseDownloader
{
    public function doDownload(ReportLogbook $request): bool
    {
        $yLib = new YahooLibrary();


        $info = $request->info;
        $jobId = $info['job_id'] ?? null;

        $date_end = $request->date_end;

        if(empty($jobId)) {
            //schedule report

            
            $response = $yLib->scheduleSearchTypeDailyReport([
                'mrkt_id'       => $request->market,
                'date_begin'    => $date_end,
                'date_end'      => $date_end,
            ]);
            


            if($response->status == false) {
                Log::warning(json_encode(
                    [
                        'log' => '[ARC][Yahoo][YahooDownloadDownloader]',
                        'log_type' => 'warning',
                        'message' => $response->message ?? 'No Data Available',
                        'request' => [
                            'mrkt_id'       => $request->market,
                            'date_begin'    => $date_end,
                            'date_end'      => $date_end,
                        ],
                        'response' => $response
                    ]
                ));
                if(isset($response->error) && $response->error == 'WS_RateLimit Reached') {
                    $err = '[ARC][Yahoo][YahooDownloadDownloader] ' . ( $response->message ?? $response->error);
                    throw new ReportException($err, $request->source, $request->date_end);
                }
                return false;
            }
            else {
                Log::info(json_encode(
                    [
                        'log' => '[ARC][Yahoo][YahooDownloadDownloader]',
                        'log_type' => 'info',
                        'message' => 'Request to Schedule New Search Type Daily Report',
                        'request' => [
                            'mrkt_id'       => $request->market,
                            'date_begin'    => $date_end,
                            'date_end'      => $date_end,
                        ],
                        'response' => $response
                    ]
                ));
                $info['job_id'] = $response->data->ID;
                $request->info = $info;
                return false;
            }
        }
        else {
            //try to download
            $response = $yLib->downloadReport($jobId);

            if($response->status == false) {
                Log::warning(json_encode(
                    [
                        'log' => '[ARC][Yahoo][YahooDownloadDownloader]',
                        'log_type' => 'warning',
                        'message' => 'downloadReport Failed, going to reschedule the download',
                        'request' => [
                            'mrkt_id'       => $request->market,
                            'date_begin'    => $date_end,
                            'date_end'      => $date_end,
                            'jobId'         => $jobId
                        ],
                        'response' => $response
                    ]
                    ));

                $info['job_id'] = '';
                $request->info = $info;
                return false;
            } else {
                if($response->status == true) {
                    $report_status = $response->data->report_status;

                    if($report_status == 'Completed') {
                        //to download file
                        $reportFile = ReportUtils::suggestOriginalLocalReportFullPath($request);
                        $dwF = $yLib->downloadFile($jobId, $response->data->report_output_file, $reportFile);
                        if($dwF->status == true) {
                            $request->infoOriginalLocalReport = $reportFile;
                            Log::info(json_encode(
                                [
                                    'log' => '[ARC][Yahoo][YahooDownloadDownloader]',
                                    'log_type' => 'info',
                                    'message' => 'Scheduled Report Downloaded',
                                    'request' => [
                                        'jobId' => $jobId,
                                        'reportOutputFile' => $response->data->report_output_file,
                                        'localReportFile' => $reportFile
                                    ],
                                    'response' => $dwF
                                ]
                            ));
                            return true;
                        }

                    } elseif($report_status == 'Failed') {
                        Log::warning(json_encode(
                            [
                                'log' => '[ARC][Yahoo][YahooDownloadDownloader]',
                                'log_type' => 'warning',
                                'message' => 'downloadReport Failed, going to reschedule the download',
                                'request' => [
                                    'mrkt_id'       => $request->market,
                                    'date_begin'    => $date_end,
                                    'date_end'      => $date_end,
                                    'jobId'         => $jobId
                                ],
                                'response' => $response
                            ]
                            ));
        
                        $info['job_id'] = '';
                        $request->info = $info;
                        return false;
                    } else {
                        Log::warning(json_encode(
                            [
                                'log' => '[ARC][Yahoo][YahooDownloadDownloader]',
                                'log_type' => 'warning',
                                'message' => 'Report not complete, need to wait',
                                'request' => [
                                    'mrkt_id'       => $request->market,
                                    'date_begin'    => $date_end,
                                    'date_end'      => $date_end,
                                    'jobId'         => $jobId
                                ],
                                'response' => $response
                            ]
                            ));
    
                        return false;
                    }
                }
            }
        }

        return false;
    }
}
