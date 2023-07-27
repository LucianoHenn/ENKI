<?php

namespace App\Services\ARC\Sources\Providers\BingAdsRevenue\Daily;

use App\Services\ARC\Sources\Abstracts\BaseImporter;
use App\Models\CurrencyConversion;
use App\Models\ARC\ReportLogbook;
use App\Models\ARC\Providers\BingAdsRevenue\BingAdsRevenueDailyReportData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use Exception;
use Illuminate\Support\Carbon as Carbon;
use Illuminate\Support\Facades\Storage as Storage;
use App\Models\ClientArcAssociation;

use App\Models\Cberry\TypeTagsTaxonomy;

/**
 * Class BingAdsRevenueDailyImporter
 */
class BingAdsRevenueDailyImporter extends BaseImporter
{

    public $send_created_event = false;
    //public $insert_chunk_size = 10;

    public function doImport(ReportLogbook $request)
    {
        $identifier = $request->identifier;
        $table = $this->getReportTableName($request);

        Log::info("[BingAdsRevenueDailyImporter] Start importing for identifier {$identifier}");

        try {
            $count = 0;
            // Read the CSV
            $localReport = $request->infoOriginalLocalReport;

            $json = json_decode(Storage::disk('system')->get($localReport));

            // Check CSV size
            if (empty($json)) { // Note: depends from source
                Log::info("[BingAdsRevenueDailyImporter] Empty data on {$localReport}");
                return false;
            }


            //NEED TO PACK BY DATE
            $dateChunks = [];
            foreach($json as $el) {
                if(isset($el->AdUnitId)) {
                    $dateChunks[$el->Date][] = $el;
                }
            }

            // Clear data BEFORE importing new data
            $this->deleteData($request);


            $validAssociations = ClientArcAssociation::where('source', $this->source)
            ->inPeriod($request->date_end)
            ->get();

            $activeAssoc = [];
            foreach($validAssociations as $assoc) {
                $activeAssoc[$assoc->info['ad_unit_id']] = $assoc;
            }


            foreach ($dateChunks as $dt => $dtChunk) {
                list($req, $tbl) = $this->retriveDateRequestAndTable($dt, $request);
                collect($dtChunk)->chunk($this->insert_chunk_size)->each(function ($chunkRows) use ($request, $table, $tbl, $activeAssoc, &$count) {
                    $toInsert = [];
                    foreach ($chunkRows as $Row) {
                        $record = $this->processRecord($Row, $request, $activeAssoc);

                        if (!empty($record)) {
                            $count++;
                            $toInsert[] = $record;
                        }
                    }

                    // Can we use also a factory method
                    $this->insert($table, $toInsert);
                });
            }


            \Log::info("[BingAdsRevenueDailyReportData] All datas imported for {$this->source} {$identifier} {$request->date_end}: {$count} Rows");
            return $count;
        } catch (Exception $e) {
            // In case of exception, set update failed and rollBack DB
            throw $e;
        }
    }
    private function processRecord($row, ReportLogbook $request, array $activeAssoc)
    {
        if(!isset($row->AdUnitId)) return null;
        
        $taxonomy = TypeTagsTaxonomy::where('DATE', $row->Date)
            ->where('ADUNIT', $row->AdUnitId)
            ->where('TYPETAG', $row->TypeTag)->first();

        $currency = 'USD';

        $date = $row->Date;
        $amount = $amount_usd = $amount_eur = $row->EstimatedNetRevenue ?? 0;
        
        $amount_eur = CurrencyConversion::convertAmount($date, $currency, 'EUR', $amount, 4, true);
        $amount_usd = CurrencyConversion::convertAmount($date, $currency, 'USD', $amount, 4, true);

        $aAssoc = $activeAssoc[$row->AdUnitId] ?? null;


        $record = [
            'date' => $date,
            'identifier' => $request->identifier,
            'client_id' => $aAssoc->client_id ?? 0,
            'market_id' => $aAssoc->market_id ?? 0, 
            'market' => $aAssoc->market->code ?? '',
    
            /* INSERT YOUR COLUMNS AFTER THIS LINE */
            'hash' => BingAdsRevenueDailyReportData::getHash([
                $row->AdUnitId,$row->TypeTag,$row->Market, $row->DeviceType
            ]),
            'ad_unit_name' => $row->AdUnitName,
            'ad_unit_id' => $row->AdUnitId,
            'type_tag' => $row->TypeTag,
    
            'clicks'  => str_replace(',', '', $row->Clicks),
            'estimated_net_revenue' => $row->EstimatedNetRevenue,
            'impressions' => str_replace(',', '', $row->Impressions),
            'non_billable_srpvs' => str_replace(',', '', $row->NonBillableSRPVs),
            'raw_srpvs' => str_replace(',', '', $row->RawSRPVs),
            'report_market' => $row->Market,
            'device_type' => $row->DeviceType,
    
            'keyword' => $taxonomy->KEYWORD ?? '',
            'campaign' => $taxonomy->CAMPAIGN ?? '',
            'source' => $taxonomy->SRC ?? '',
            'adset_id' => $taxonomy->ADSETID ?? '',
            'website' => $taxonomy->WEBSITE ?? '',
            'campaign_id' => $taxonomy->CAMPAIGNID ?? '',
            'counter' => $taxonomy->COUNTER ?? '',
            'user_id' => $taxonomy->USERID ?? '',
            'country' => $taxonomy->COUNTRY ?? '',
            

            'revenue_share' => 1,
            'currency' => $currency,
            'amount' => $amount,
            'amount_eur' => $amount_eur,
            'amount_usd' => $amount_usd,
    
            'created_at' => now(),
            'updated_at' => now()
        ];

        return $record;
        
    }
}
