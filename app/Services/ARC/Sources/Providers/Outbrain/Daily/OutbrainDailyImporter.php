<?php

namespace App\Services\ARC\Sources\Providers\Outbrain\Daily;

use App\Services\ARC\Sources\Abstracts\BaseImporter;
use App\Models\CurrencyConversion;
use App\Models\ARC\ReportLogbook;
use App\Models\ARC\Providers\Outbrain\OutbrainDailyReportData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use League\Csv\Statement;
use League\Csv\Reader;

use Exception;
use Illuminate\Support\Carbon as Carbon;
use Illuminate\Support\Facades\Storage as Storage;
use App\Models\ClientArcAssociation;

/**
 * Class OutbrainDailyImporter
 */
class OutbrainDailyImporter extends BaseImporter
{

    public $send_created_event = false;

    public function doImport(ReportLogbook $request)
    {
        $identifier = $request->identifier;
        $table = $this->getReportTableName($request);

        Log::info("[OutbrainDailyImporter] Start importing for identifier {$identifier}");

        try {
            $count = 0;
            // Read the CSV
            $localReport = $request->infoOriginalLocalReport;

            $json = json_decode(Storage::disk('system')->get($localReport));


            // Check CSV size
            if (empty($json) || empty($json->summary->spend)) { // Note: depends from source
                Log::info("[OutbrainDailyImporter] Empty data on {$localReport}");
                return false;
            }

            // Clear data BEFORE importing new data
            $this->deleteData($request);

            $validAssociations = ClientArcAssociation::where('source', $this->source)
            ->inPeriod($request->date_end)
            ->get();

            $activeAssoc = null;
            foreach ($validAssociations as $assoc) {
                if ($assoc->info['account_id'] == $request->identifier) {
                    $activeAssoc = $assoc;
                    break;
                }
            }

            collect($json->results)
            ->chunk($this->insert_chunk_size)
            ->each(function ($chunkDataRows) use ($request, $table, $identifier, $activeAssoc, &$count) {
                $toInsert = [];
                foreach ($chunkDataRows as $dataRow) {
                    $record = $this->processRecord($dataRow, $request, $activeAssoc);
                    $toInsert[] = $record;
                    $count++;
                }
                // Can we use also a factory method
                $this->insert($table, $toInsert);
            });

            return $count;
        } catch (Exception $e) {
            // In case of exception, set update failed and rollBack DB
            throw $e;
        }
    }

    private function processRecord($row, ReportLogbook $request, ClientArcAssociation $activeAssoc)
    {
        $currency = strtoupper($row->metadata->budget->currency);
        $amount = $row->metrics->spend ?? 0;
        $amount_eur = $amount_usd = 0;
        $date   = $request->date_end;

        if ($currency === "EUR") {
            $amount_eur = $amount;
            $amount_usd = CurrencyConversion::convertAmount($date, $currency, 'USD', $amount, 4, true);
        } else { //$currency === "USD") {
            $amount_usd = $amount;
            $amount_eur = CurrencyConversion::convertAmount($date, $currency, 'EUR', $amount, 4, true);
        }
        $timestamp = Carbon::now();

        $conversion_metrics= [];

        if(!empty($row->metrics->conversionMetrics)) {
            foreach($row->metrics->conversionMetrics as $cM) {
                $conversion_metrics[ $cM->name ] = $cM;
            }
        }

        $record = [
            'date'                  => $date,
            'identifier'            => $request->identifier,

            'client_id'             => $activeAssoc->client_id,
            'market_id'             => $activeAssoc->market_id,
            'market'                => $activeAssoc->market->code,

            'account_id'            => $request->identifier,

            
            'campaign_id'           => $row->metadata->id,
            'campaign'              => $row->metadata->name,
            'impressions'           => $row->metrics->impressions,
            'clicks'                => $row->metrics->clicks,
            'conversions'           => $row->metrics->conversions,
            'metadata'              => json_encode($row->metadata),
            'metrics'               => json_encode($row->metrics),
            'conversion_metrics'    => json_encode($conversion_metrics),


            'currency'              => $currency,
            'revenue_share'         => 1,
            'amount'                => $amount,
            'amount_eur'            => $amount_eur,
            'amount_usd'            => $amount_usd,

            'created_at'            => $timestamp,
            'updated_at'            => $timestamp,
        ];
    
        return $record;
    }
}
