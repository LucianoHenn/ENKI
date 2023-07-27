<?php

namespace App\Services\ARC\Sources\Providers\System1\Subid;

use App\Services\ARC\Sources\Abstracts\BaseImporter;
use App\Models\CurrencyConversion;
use App\Models\ARC\ReportLogbook;
use App\Models\ARC\Providers\System1SubidReportData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


use Exception;
use Illuminate\Support\Carbon as Carbon;
use Illuminate\Support\Facades\Storage as Storage;
use App\Models\ClientArcAssociation;
/**
 * Class System1SubidImporter
 */
class System1SubidImporter extends BaseImporter
{

    public $send_created_event = false;

    public function doImport(ReportLogbook $request)
    {
        $identifier = $request->identifier;
        $table = $this->getReportTableName($request);

        Log::info("[System1SubidImporter] Start importing for identifier {$identifier}");

        try {
            $localReport = $request->infoOriginalLocalReport;

            $data = json_decode(Storage::disk('system')->get($localReport), true);
            $totalItems = 0;
            if (!empty($data)) {
                $totalItems = count($data) ?? 0;
            }

            // Check Data size
            if ($totalItems < 1) { // Note: depends from source
                Log::info("[System1SubidImporter] Empty data on {$localReport}");
                return false;
            }

            // Clear data BEFORE importing new data
            $this->deleteData($request);

            $validAssociations = ClientArcAssociation::where('source', $this->source)
                ->inPeriod($request->date_end)
                ->get();

            $activeAssoc = null;
            foreach ($validAssociations as $assoc) {
                if ($assoc->info['domain'] == $request->identifier) {
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
                    // Can we use also a factory method
                    $this->insert($table, $toInsert);
                });

            return $totalItems;
        } catch (Exception $e) {
            // In case of exception, set update failed and rollBack DB
            Log::error('[System1SubidImporter] ' . $e->getMessage());
            throw $e;
        }
    }

    private function processRecord($row, ReportLogbook $request, $activeAssoc)
    {

        $device = $row['Device'];

        $revenue_share = $this->getRevenueShare($row['Date']);

        $currency = 'USD';

        $amount = $row['Revenue'] ?? 0;
        $amount_eur = $amount_usd = 0;

        if ($currency === "EUR") {
            $amount_eur = $amount;
            $amount_usd = CurrencyConversion::convertAmount($row['Date'], $currency, 'USD', $amount, 4, true);
        } else { //$currency === "USD") {
            $amount_usd = $amount;
            $amount_eur = CurrencyConversion::convertAmount($row['Date'], $currency, 'EUR', $amount, 4, true);
        }

        $timestamp = Carbon::now();

        $subIdInfo = $row['Subid'];

        $utm_source = $subIdInfo['utm_source'] ?? '';
        if ($utm_source != 'bing' && $utm_source != 'google') {
            $utm_source = 'google';
        }


        $record = [
            'date'                  => $row['Date'],
            'identifier'            => $request->identifier,

            'client_id'             => $activeAssoc->client_id,
            'market_id'             => $activeAssoc->market_id,
            'market'                => $activeAssoc->market->code,

            'hash'                  => System1SubidReportData::getHash([$row['Subid'], $row['Segment'], $row['Device'],$row['Country']]),
            'source'                => $utm_source,
            'segment' => $row['Segment'],
            'device' => $row['Device'],
            'country' => $row['Country'],
            'subid' => json_encode($row['Subid']),

            'sessions' => $row['Sessions'],
            'impressions' => $row['Impressions'],
            'pageviews' => $row['Pageviews'],
            'paid_clicks' => $row['Paid Clicks'],

            'revenue_share' => $revenue_share,
            'currency' => $currency,
            'amount' => $amount,
            'amount_eur' => $amount_eur,
            'amount_usd' => $amount_usd,

            'created_at' => $timestamp,
            'updated_at' => $timestamp
        ];


        return $record;
    }

    public function getRevenueShare($date)
    {
        if($date < '2023-01-01') {
            return 0.850;    
        }
        return 0.750;
    }
}
