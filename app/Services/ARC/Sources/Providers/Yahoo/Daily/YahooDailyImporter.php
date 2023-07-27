<?php

namespace App\Services\ARC\Sources\Providers\Yahoo\Daily;

use App\Services\ARC\Sources\Abstracts\BaseImporter;
use App\Models\CurrencyConversion;
use App\Models\ARC\ReportLogbook;
use App\Models\ARC\Providers\Yahoo\YahooDailyReportData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\ServiceProviderConfig;


use Exception;
use Illuminate\Support\Carbon as Carbon;
use Illuminate\Support\Facades\Storage as Storage;
use App\Models\ClientArcAssociation;
use App\Services\YahooAssociations;
/**
 * Class YahooDailyImporter
 */
class YahooDailyImporter extends BaseImporter
{

    public $send_created_event = false;
    public $currency = 'USD';
    public $revenue_share = 0.75;
    public $service_configs;

    public $insert_chunk_size = 5000; 
    public $export_pack_limit = 5000;

    public function doImport(ReportLogbook $request)
    {
        $identifier = $request->identifier;
        

        Log::info("[YahooDailyImporter] Start importing for identifier {$identifier}");

        try {
            $count = 0;
            // Read the JSON
            $localReport = $request->infoOriginalLocalReport;

            // Set read file and set header
            $json = json_decode(file_get_contents($localReport));



            if (isset($json->ResultSet->Row->Message)) {
                Log::info("[YahooDailyImporter] Empty data on {$localReport}, " . $json->ResultSet->Row->Message);
                return false;
            }

            // Clear data BEFORE importing new data
            $this->deleteData($request);

            //getting the response currency
            $this->currency = $json->MetaInfo->currency;

            //we need to pack data by date
            $dateChunks = [];
            foreach ($json->ResultSet->Row as $row) {
                
                preg_match('/(\d\d\d\d)(\d\d)(\d\d)/', $row->DATA_DATE, $match);
                if(!empty($match)) {

                    $dt = implode('-', [$match[1], $match[2], $match[3]]);
                    
                    $row->date = $dt;
                    
                    $dateChunks[$dt][] = $row;
                }
            }

            $this->service_configs = ServiceProviderConfig::all();
            $this->service_configs->keyBy('id');
            Log::info("[YahooDailyImporter] Iterating through JSON file {$localReport}");
            foreach ($dateChunks as $dt => $dtChunk) {
                list($req, $tbl) = $this->retriveDateRequestAndTable($dt, $request);
                //set table

                collect($dtChunk)
                    ->chunk($this->insert_chunk_size)
                    ->each(function ($chunkDataRows) use ($request, $tbl, $dt, $identifier, &$count) {
                        $toInsert = [];
                        foreach ($chunkDataRows as $dataRow) {
                            $record = $this->processRecord($dataRow, $request);
                            $toInsert[] = $record;
                            $count++;
                        }
                        // Can we use also a factory method
                        $this->insert($tbl, $toInsert);
                    });
            }

            return $count;
        } catch (Exception $e) {
            // In case of exception, set update failed and rollBack DB
            throw $e;
        }
    }

    private function processRecord($row, ReportLogbook $request)
    {
        
        $currency = $this->currency; 
        $device = $row->DEVICE_TYPE;



        $amount = $row->ESTIMATED_GROSS_REVENUE ?? 0;
        $amount_eur = $amount_usd = 0;

        if ($currency === "EUR") {
            $amount_eur = $amount;
            $amount_usd = CurrencyConversion::convertAmount($row->date, $currency, 'USD', $amount, 4, true);
        } else { //$currency === "USD") {
            $amount_usd = $amount;
            $amount_eur = CurrencyConversion::convertAmount($row->date, $currency, 'EUR', $amount, 4, true);
        }

        $config_id = 0;
        $config_name = '';
        $client_id = 0;
        if(strlen($row->TYPE_TAG) > 40) {
            $hash = substr($row->TYPE_TAG, 0, 40);
            $config_id = substr($row->TYPE_TAG, 40);
            
            $cfg = $this->service_configs->find($config_id);

            if(!is_null($cfg)) {
                $config_name = $cfg->name;
                $client_id = $cfg->client_id;
            }
        }


        $info = YahooAssociations::get($hash);

        if(is_null($info)) {
            $info = [];
        }
        else {
            $info = json_decode($info->info);
        }

        $sub_id = $info->sub_id ?? $hash;

        $timestamp = Carbon::now();

        $record = [
            'date'        => $row->date,
            'identifier' => $request->identifier,
            'config_id' => $config_id,
            'config_name' => $config_name,
            'client_id' => $client_id,


            'sub_id' => $sub_id,
            'sub_id_hash' => $hash,
            'sub_id_info' => json_encode($info),

            'market' => $request->market,
            'ad_type' => !empty($row->AD_TYPE) ? $row->AD_TYPE : 'TA',

            'type_tag' => $row->TYPE_TAG,
            'source_tag' => $row->SOURCE_TAG,
            'tq_score' => $row->TQ_SCORE,

            'device' => $device,

            'searches' => $row->SEARCHES ?? 0,
            'bidded_searches' => $row->BIDDED_SEARCHES ?? 0,
            'bidded_results' => $row->BIDDED_RESULTS ?? 0,
            'bidded_clicks' => $row->BIDDED_CLICKS ?? 0,

            'revenue_share' => $this->revenue_share,
            'currency' => $currency,
            'amount' => $amount,
            'amount_eur' => $amount_eur,
            'amount_usd' => $amount_usd,

            'created_at' => $timestamp,
            'updated_at' => $timestamp
        ];

        return $record;
    }
}
