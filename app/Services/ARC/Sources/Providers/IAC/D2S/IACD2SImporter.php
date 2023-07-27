<?php

namespace App\Services\ARC\Sources\Providers\IAC\D2S;

use App\Services\ARC\Sources\Abstracts\BaseImporter;
use App\Models\CurrencyConversion;
use App\Models\ARC\ReportLogbook;
use App\Models\ARC\Providers\IACD2SReportData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


use Exception;
use Illuminate\Support\Carbon as Carbon;
use Illuminate\Support\Facades\Storage as Storage;

use League\Csv\Statement;
use League\Csv\Reader;

use App\Models\ClientArcAssociation;
use App\Models\Bbtrk\Rs4cAssociation;

/**
 * Class IACD2SImporter
 */
class IACD2SImporter extends BaseImporter
{

    public $send_created_event = false;
    public $currency = 'USD';
    public $revenue_share = 1;
    public $insert_chunk_size = 250;

    protected $activeAssociations = [];
    protected $r4csAssociations = [];

    public function doImport(ReportLogbook $request)
    {
        $identifier = $request->identifier;
        Log::info("[IACD2SImporter] Start importing for identifier {$identifier}");

        try {
            $count = 0;
            // Read the CSV
            $localReport = $request->infoOriginalLocalReport;

            // Set read file and set header
            $csv = Reader::createFromPath($localReport, 'r');
            //$csv->setHeaderOffset(1);

            // Check CSV size
            if ($csv->count() <= 0) { // Note: depends from source
                Log::info("[IACD2SImporter] Empty data on {$localReport}");
                return false;
            }

            $dateChunks = [];

            $clientIdentifier = null;

            $stmt = (new Statement())->offset(1)->limit($csv->count() - 2);
            $csvRows = $stmt->process($csv);

            /*
                array:13 [
                0 => "1"
                1 => "2022-02-08" //DATE
                2 => "aj-cm9" // CLIENTID
                3 => "us6" //channel
                4 => "Google" //REvenue Source
                5 => "Unknown" // Market
                6 => "desktop" / platform
                7 => "2" / queries
                8 => "10" / impressions
                9 => "2" / clicks
                10 => "$947.10" //page rpm
                11 => "$189.42" //impression rpm
                12 => "$1.89" //net revenue
                ]
            */
            
            foreach ($csvRows as $rowArr) {

                $row = (object) [
                    'date'           => $rowArr[1] ?? '',
                    'client_id'      => $rowArr[2] ?? '',
                    'channel'        => $rowArr[3] ?? '',
                    'revenue_source' => $rowArr[4] ?? '',
                    'market'         => $rowArr[5] ?? '',
                    'platform'       => $rowArr[6] ?? '',
                    'queries'        => str_replace(',', '', ($rowArr[7] ?? '')),
                    'impressions'       => str_replace(',', '', ($rowArr[8] ?? '')),
                    'clicks'            => str_replace(',', '', ($rowArr[9] ?? '')),
                    'page_rpm'          => str_replace(['$', ','], [''], ($rowArr[10] ?? '')),
                    'impression_rpm'    => str_replace(['$', ','], [''], ($rowArr[11] ?? '')),
                    'net_revenue'       => str_replace(['$', ','], [''], ($rowArr[12] ?? ''))
                ];

                
                if (empty($row->date) || $row->date > $request->date_end) { 
                    continue;
                }


                $clientIdentifier = $row->client_id;
                if (!isset($dateChunks[$row->date])) {
                    $dateChunks[$row->date] = [];
                }
                $dateChunks[$row->date][] = $row;
            }


            

            // Clear data BEFORE importing new data
            try {
                $reqClone = clone ($request);
                $reqClone->identifier = $clientIdentifier; // we clear the data of the clientID of the report
                $this->deleteData($request);
            } catch (\Exception $e) {
                Log::warning('[IACD2SImporter]' . $e->getMessage());
            }

            // TODO
            foreach ($dateChunks as $dt => $dtChunk) {

                list($req, $tbl) = $this->retriveDateRequestAndTable($dt, $request);
                //set table

                collect($dtChunk)
                    ->chunk($this->insert_chunk_size)
                    ->each(function ($chunkDataRows) use ($request, $tbl, $dt, $identifier,  &$count) {
                        $toInsert = [];
                        foreach ($chunkDataRows as $dataRow) {
                            $record = $this->processRecord($dataRow, $request);
                            $toInsert[] = $record;
                            $count++;
                        }

                        try {
                            $x = new IACD2SReportData();
                            $x->setTable($tbl)->insert(
                                $toInsert
                            );
                        } catch (\Exception $e) {
                            Log::warning('[IACD2SImporter] doImport, insert Exception: ' . $e->getMessage());
                        }
                    });
            }

            return $count;
        } catch (Exception $e) {
            // In case of exception, set update failed and rollBack DB
            throw $e;
        }
    }

    protected function processRecord($dataRow, $request)
    {
        $rs4c = $this->getRs4cAssociation($dataRow->date, $dataRow->client_id, $dataRow->channel);

        $assoc = null;
        $rs4cIdentifierLabel = null;
        if (is_null($rs4c)) {
            Log::warning('[IACD2SImporter] Unable to find a matching association on rs4c: ' . json_encode($dataRow));
        } else {
            $assoc = $this->getActiveAssociation($dataRow->date, $dataRow->client_id, $rs4c->source);


            $u = parse_url($rs4c->lp_url);
            $query = [];
            parse_str($u['query'] ?? '', $query);

            if (!empty($query['cmp'])) {
                $rs4cIdentifierLabel = trim($query['cmp']);
            }
        }

        $amount = $dataRow->net_revenue;

        $amount_eur = $amount_usd = 0;

        if ($this->currency === "EUR") {
            $amount_eur = $amount;
            $amount_usd = CurrencyConversion::convertAmount($dataRow->date, $this->currency, 'USD', $amount, 4, true);
        } else { //$currency === "USD") {
            $amount_usd = $amount;
            $amount_eur = CurrencyConversion::convertAmount($dataRow->date, $this->currency, 'EUR', $amount, 4, true);
        }


        $timestamp = now();
        $record = [
            'date'          => $dataRow->date,
            'identifier'    => $dataRow->client_id,
            'client_id'     => $assoc->client_id ?? 0,
            'hash'          => IACD2SReportData::getHash([
                $dataRow->client_id,
                $dataRow->channel,
                $dataRow->revenue_source,
                $dataRow->market,
                $dataRow->platform
            ]),
            'in_source'     => $rs4c->source ?? 'unknown',

            'partner_client_id'     => $dataRow->client_id,
            'partner_channel'       => $dataRow->channel,
            'partner_revenue_source'       => $dataRow->revenue_source,
            'market'                => $dataRow->market,
            'platform'              => $dataRow->platform,

            'rs4c_identifier'       => $rs4c->identifier ?? 'unknown',
            'rs4c_identifier_label' => $rs4cIdentifierLabel ?? 'unknown',

            'queries'               => $dataRow->queries,
            'impressions'           => $dataRow->impressions,
            'clicks'                => $dataRow->clicks,

            'page_rpm'              => $dataRow->page_rpm,
            'impression_rpm'        => $dataRow->impression_rpm,

            'revenue_share'         => $this->revenue_share,
            'currency'              => $this->currency,
            'amount'                => $amount,
            'amount_eur'            => $amount_eur,
            'amount_usd'            => $amount_usd,

            'created_at'            => $timestamp,
            'updated_at'            => $timestamp
        ];

        return $record;
    }

    protected function getRs4cAssociation($date, $client_id, $channel)
    {
        $key = sha1($date . $client_id . $channel);

        if (isset($this->activeAssociations[$key]) && !is_null($this->activeAssociations[$key])) {
            return $this->activeAssociations[$key];
        }

        $validAssociation = Rs4cAssociation::where('date', $date)
            ->where('rch', str_replace('us', '', $channel))->first();

        if (!is_null($validAssociation)) {
            $this->activeAssociations[$key] = $validAssociation;
            return $this->activeAssociations[$key];
        }

        return null;
    }

    protected function getActiveAssociation($date, $client_id, $source)
    {
        $key = sha1($date . $client_id . $source);

        if (isset($this->activeAssociations[$key]) && !is_null($this->activeAssociations[$key])) {
            return $this->activeAssociations[$key];
        }

        $validAssociations = ClientArcAssociation::where('source', $this->source)
            ->inPeriod($date)
            ->get();

        $activeAssoc = null;
        foreach ($validAssociations as $assoc) {
            if ($assoc->info['client_id'] == $client_id && $assoc->info['src'] == $source) {
                $this->activeAssociations[$key] = $assoc;
                return $assoc;
                break;
            }
        }

        return null;
    }
}
