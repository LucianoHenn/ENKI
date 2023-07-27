<?php

namespace App\Services\ARC\Sources\Providers\Yahoo\Hourly;

use Illuminate\Support\Facades\Storage;

use App\Exceptions\ARC\ReportException;
use App\Services\ARC\File\ReportUtils;
use App\Services\ARC\Sources\Abstracts\BaseDownloader;
use App\Models\ARC\ReportLogbook;
use Illuminate\Support\Facades\Log;
use App\Services\ARC\Sources\Providers\Yahoo\YahooLibrary;
use Carbon\Carbon;

/**
 * Class YahooHourlyDownloader
 */
class YahooHourlyDownloader extends BaseDownloader
{
    public function doDownload(ReportLogbook $request): bool
    {
        $yLib = new YahooLibrary();


        $info = $request->info;
        $jobId = $info['job_id'] ?? null;

        $date_begin = Carbon::createFromFormat('Y-m-d', $request->date_end)->subDays(1)->format('Y-m-d');
        $request->date_begin = $date_begin;
        $date_end = $request->date_end;

        if(empty($jobId)) {
            //schedule report

            $end_hour = '23';

            if(today()->format('Y-m-d') == $request->date_end) {
                $end_hour = max(0, date('H')-4);
                if(strlen($end_hour) == 1) {
                    $end_hour = '0'.$end_hour;
                }
            }
            $response = $yLib->scheduleSearchTypeHourlyReport([
                'mrkt_id'       => $request->market,
                'date_begin'    => $date_begin .'00',
                'date_end'      => $date_end . $end_hour,
            ]);
            Log::info(json_encode(
                [
                    'log' => '[ARC][Yahoo][YahooHourlyDownloader]',
                    'log_type' => 'info',
                    'message' => 'Request to Schedule New Search Type Hourly Report',
                    'request' => [
                        'mrkt_id'       => $request->market,
                        'date_begin'    => $date_begin .'00',
                        'date_end'      => $date_end . $end_hour,
                    ],
                    'response' => $response
                ]
            ));

            if($response->status == false) {
                if(isset($response->error) && $response->error == 'WS_RateLimit Reached') {
                    $err = '[ARC][Yahoo][YahooDownloadDownloader] ' . ( $response->message ?? $response->error);
                    throw new ReportException($err, $request->source, $request->date_end);
                }
                return false;
            }
            else {
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
                        'log' => '[ARC][Yahoo][YahooHourlyDownloader]',
                        'log_type' => 'warning',
                        'message' => 'downloadReport Failed, going to reschedule the download',
                        'request' => [
                            'mrkt_id'       => $request->market,
                            'date_begin'    => $date_begin .'00',
                            'date_end'      => $date_end .'23',
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
                                    'log' => '[ARC][Yahoo][YahooHourlyDownloader]',
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
                                'log' => '[ARC][Yahoo][YahooHourlyDownloader]',
                                'log_type' => 'warning',
                                'message' => 'downloadReport Failed, going to reschedule the download',
                                'request' => [
                                    'mrkt_id'       => $request->market,
                                    'date_begin'    => $date_begin .'00',
                                    'date_end'      => $date_end .'23',
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
                                'log' => '[ARC][Yahoo][YahooHourlyDownloader]',
                                'log_type' => 'warning',
                                'message' => 'Report not complete, need to wait',
                                'request' => [
                                    'mrkt_id'       => $request->market,
                                    'date_begin'    => $date_begin .'00',
                                    'date_end'      => $date_end .'23',
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
