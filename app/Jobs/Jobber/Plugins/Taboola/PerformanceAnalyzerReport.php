<?php

namespace App\Jobs\Jobber\Plugins\Taboola;

use App\Jobs\Jobber\BaseJobber;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\ARC\ReportLogbook;
use Throwable;
use InvalidArgumentException;
use Aws\Athena\AthenaClient as Athena;
use App\Services\ARC\File\ReportUtils;
use App\Helpers\XLSXWriter;
use DateTime;

use Illuminate\Support\Facades\Log;

/**
 * Class CreateCampaigns
 */
class PerformanceAnalyzerReport extends BaseJobber implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function enqueue(
        array $args = null
    ) {
        $this->jobManager->markAsRetryable();
        $this->checkImports('Yahoo');
        $this->checkImports('Taboola');
    }

    public static function validate(array $args)
    {
    }



    /**
     * Execute the job.
     *
     * @return void
     */
    public function run()
    {

        $athenaClient = new Athena([
            'version'   => config('services.enki-report-athena.version'),
            'region'    => config('services.enki-report-athena.region'),
            'credentials' => [
                'key'       => config('services.enki-report-athena.key'),
                'secret'    => config('services.enki-report-athena.secret'),
            ]
        ]);

        $attributes = [
            'y.date',
            't.identifier',
            'y.campaignid',
            'y.campaign',
            'y.adsetid',
            't.in_clicks',
            't.cost_eur',
            't.cost_usd'
        ];

        $metrics = [
            'SUM (y.bidded_clicks) AS out_clicks',
            'SUM(y.amount_eur * y.revenue_share) AS net_revenue_eur',
            'SUM(y.amount_usd * y.revenue_share) AS net_revenue_usd',
            'SUM(y.amount_eur * y.revenue_share) - t.cost_eur AS profit_eur',
            'SUM(y.amount_usd * y.revenue_share) - t.cost_usd AS profit_usd',
            'CAST(
                    (SUM(y.amount_usd * y.revenue_share) - t.cost_usd) / t.cost_usd AS decimal(14, 2)
                ) as roi'
        ];

        $where = [
            'y.date >= date(\'' . $this->args['date_begin'] . '\')',
            'y.date <= date(\'' . $this->args['date'] . '\')'
        ];

        $query = 'SELECT ' . implode(',', array_merge(
                $attributes,
                $metrics
            ));

        $query .= ' FROM "enki_reports"."vw_yahoo_daily_last_three_months" y
            join (
                SELECT identifier,
                    campaign_id,
                    date,
                    SUM(CAST(clicks AS INTEGER)) AS in_clicks,
                    SUM(CAST(amount_eur as decimal(14, 4))) AS cost_eur,
                    SUM(CAST(amount_usd as decimal(14, 4))) AS cost_usd
                FROM "enki_reports"."vw_taboola_daily_last_three_months"
                WHERE CAST(amount AS decimal(14,4)) > 0 AND ' . 'date >= date(\'' . $this->args['date_begin'] . '\')' .
            ' AND date <= date(\'' . $this->args['date'] . '\')' .
            ' AND identifier IN(\'' . implode('\',\'', $this->args['ad_accounts']) . '\')'  .
            'GROUP BY date,
                identifier,
                campaign_id
                        ) t ON (
                            t.campaign_id = y.campaignid
                            AND t.date = y.date
                        )';

        $query .= ' WHERE ' . implode(' AND ', $where);

        $query .= ' GROUP BY ' . implode(',', $attributes);
        $query .= ' ORDER BY y.date ASC, t.identifier ASC, y.campaignid ASC';

        $result1 = $athenaClient->startQueryExecution([
            'QueryExecutionContext' => ['Database' => config('services.enki-report-athena.db_name')],
            'QueryString'   => $query,
            'ResultConfiguration' => [
                'EncryptionConfiguration' => ['EncryptionOption' => 'SSE_S3'],
                'OutputLocation' => config('services.enki-report-athena.output_location')
            ]
        ]);

        $queryExecutionId = $result1->get('QueryExecutionId');


        Log::info("[" . static::class . "][" . __FUNCTION__ . "] Waiting for Athena Query...");
        $isQueryStillRunning = true;

        // Check the status of the query execution
        while ($isQueryStillRunning) {
            $queryStatus = $athenaClient->getQueryExecution(array('QueryExecutionId' => $queryExecutionId));
            $status = $queryStatus['QueryExecution']['Status']['State'];

            if ($status === 'SUCCEEDED') {
                // The query has completed successfully
                $isQueryStillRunning = false;

                // Get the results of the query
                $result1 = $athenaClient->GetQueryResults([
                    'QueryExecutionId' => $queryExecutionId,
                    'MaxResults' => 500
                ]);
            } elseif ($status === 'FAILED' || $status === 'CANCELLED') {

                $isQueryStillRunning = false;
                Log::warning("[" . static::class . "][" . __FUNCTION__ . "] Athena Query failed or cancelled " . $queryStatus['QueryExecution']);
            } else {
                // The query is still running, wait for a while before checking again
                sleep(5); // Sleep for 5 seconds
            }
        }


        $data = $result1->get('ResultSet');

        $res = athena_processResultRows($data['Rows'], $data['ResultSetMetadata']['ColumnInfo'], true);



        if (!empty($res)) {

            Log::info("[" . static::class . "][" . __FUNCTION__ . "] Generating Xls file");

            $localFile = config('arc.tmp_path') . 'performance_reports/' . $queryExecutionId . '.xls';
            ReportUtils::createParentFolder($localFile);

            $keys = array_keys($res[0]);
            $header = array_fill_keys($keys, 'string');

            $writer = new XLSXWriter();
            $writer->writeSheet($res, 'Sheet1', $header);
            $writer->writeToFile($localFile);

            if (!file_exists($localFile)) {
                Log::info("[" . static::class . "][" . __FUNCTION__ . "] Xls not created");
                ReportUtils::deleteLocalFile($localFile);
            } else {
                Log::info("[" . static::class . "][" . __FUNCTION__ . "] Storing File in S3");
                $this->jobManager->storeFileInS3(fopen($localFile, 'r'), $this->args['date'] . '-' . $this->args['ad_accounts'][0] . '.xls');
            }
        } else {
            Log::info("[" . static::class . "][" . __FUNCTION__ . "] No data available");
        }
    }


    public function checkImports($source)
    {

        Log::info("[" . static::class . "][" . __FUNCTION__ . "] Checking Loogbooks for " . $source . ' from ' . $this->args['date_begin'] . ' to ' . $this->args['date']);

        if ($source == 'Yahoo') {
            $report_type = 'Daily';
            $req = ReportLogbook::where('source', 'Yahoo')
                ->where('report_type', $report_type)
                ->where('date_begin', '>=', $this->args['date_begin'])
                ->where('date_end', '<=', $this->args['date'])->get();
        } else {
            $req = ReportLogbook::where('source', 'Taboola')
                ->where('report_type', 'Daily')
                ->whereIn('identifier', $this->args['ad_accounts'])
                ->where('date_begin', '>=', $this->args['date_begin'])
                ->where('date_end', '<=', $this->args['date'])->get();
        }


        if ($req->count() == 0) {
            throw new InvalidArgumentException("[" . static::class . "][" . __FUNCTION__ . "] Missing import for that date range");
        } else {
            $incomplete = $failed = 0;
            foreach ($req as $e) {
                if ($e->status_id < 0) {
                    $failed++;
                } elseif ($e->status_id != 4) {
                    $incomplete++;
                }
            }
            if ($failed > 0) {
                throw new InvalidArgumentException("[" . static::class . "][" . __FUNCTION__ . "] Some imports failed");
            }
            if ($incomplete > 0) {
                throw new InvalidArgumentException("[" . static::class . "][" . __FUNCTION__ . "] Some imports are still processing");
            }
        }
    }
}
