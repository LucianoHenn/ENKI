<?php

namespace App\Services\ARC\Sources\Providers\Tonic\Daily;

use App\Services\ARC\Sources\Abstracts\BaseImporter;
use App\Models\CurrencyConversion;
use App\Models\ARC\ReportLogbook;
use App\Models\ARC\Providers\Tonic\TonicDailyReportData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


use Exception;
use Illuminate\Support\Carbon as Carbon;
use Illuminate\Support\Facades\Storage as Storage;
use App\Models\ClientArcAssociation;

/**
 * Class TonicDailyImporter
 */
class TonicDailyImporter extends BaseImporter
{

    public $send_created_event = false;

    public function doImport(ReportLogbook $request)
    {
        $identifier = $request->identifier;
        $table = $this->getReportTableName($request);

        Log::info("[TonicDailyImporter] Start importing for identifier {$identifier}");

        try {
            $localReport = $request->infoOriginalLocalReport;

            $data = json_decode(Storage::disk('system')->get($localReport));
            
            
            $totalItems = 0;
            if (!empty($data)) {
                $totalItems = count($data) ?? 0;
            }

            // Check Data size
            if ($totalItems < 1) { // Note: depends from source
                Log::info("[TonicDailyImporter] Empty data on {$localReport}");
                return false;
            }

            // Clear data BEFORE importing new data
            $this->deleteData($request);

            $validAssociations = ClientArcAssociation::where('source', $this->source)
                ->inPeriod($request->date_end)
                ->get();

            $activeAssoc = null;
            foreach ($validAssociations as $assoc) {
                if ($assoc->info['key'] == $request->identifier) {
                    $activeAssoc = $assoc;
                    break;
                }
            }

            collect($data)
                ->chunk($this->insert_chunk_size)
                ->each(function ($chunkDataRows) use ($request, $table, $activeAssoc) {
                    $toInsert = [];
                    foreach ($chunkDataRows as $dataRow) {
                        $toInsert[] = $this->processRecord($dataRow, $request, $activeAssoc);
                    }
                    $this->insert($table, $toInsert);
                });

            return $totalItems;
        } catch (Exception $e) {
            // In case of exception, set update failed and rollBack DB
            Log::error('[TonicDailyImporter] ' . $e->getMessage());
            throw $e;
        }
    }

    private function processRecord($row, ReportLogbook $request, $activeAssoc)
    {

        
        $date = $row->date;

        $currency = 'USD';

        $amount = $row->revenueUsd ?? 0;
        $amount_eur = $amount_usd = 0;

        if ($currency === "EUR") {
            $amount_eur = $amount;
            $amount_usd = CurrencyConversion::convertAmount($date, $currency, 'USD', $amount, 4, true);
        } else { //$currency === "USD") {
            $amount_usd = $amount;
            $amount_eur = CurrencyConversion::convertAmount($date, $currency, 'EUR', $amount, 4, true);
        }

        $timestamp = Carbon::now();


        $record = [
            'date'                  => $date,
            'identifier'            => $request->identifier,

            'client_id'             => $activeAssoc->client_id,
            'market_id'             => $activeAssoc->market_id,
            'market'                => $activeAssoc->market->code,

            'hash'                  => TonicDailyReportData::getHash([
                $request->identifier,
                $row->campaign_id,
                $row->subid1 ?? '',
                $row->subid2 ?? '',
                $row->subid3 ?? '',
                $row->subid4 ?? '',
                $row->keyword ?? '',
                $row->network ?? '',
                $row->site ?? '',
                $row->adtitle ?? '',
                $row->device ?? '',
                uniqid()
            ]),

            'clicks'                => $row->clicks,
            'campaign_name'         => $row->campaign_name,
            'campaign_id'           => $row->campaign_id,

            'subid1'                => $row->subid1 ?? '',
            'subid2'                => $row->subid2 ?? '',
            'subid3'                => $row->subid3 ?? '',
            'subid4'                => $row->subid4 ?? '',

            'keyword'               => $row->keyword ?? '',
            'network'               => $row->network ?? '',
            'site'                  => $row->site ?? '',
            'device'                => $row->device ?? '',
            'adtitle'               => $row->adtitle ?? '',

            'revenue_share'         => 1,
            'currency'              => $currency,
            'amount'                => $amount,
            'amount_eur' => $amount_eur,
            'amount_usd' => $amount_usd,

            'created_at' => $timestamp,
            'updated_at' => $timestamp
        ];


        return $record;
    }
}
