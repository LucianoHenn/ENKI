<?php

namespace App\Services\ARC\Sources\Providers\TikTok\AdGroups;

use App\Services\ARC\Sources\Abstracts\BaseImporter;
use App\Models\CurrencyConversion;
use App\Models\ARC\ReportLogbook;
use App\Models\ARC\Providers\TikTok\TikTokAdGroupsReportData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


use Exception;
use Illuminate\Support\Carbon as Carbon;
use Illuminate\Support\Facades\Storage as Storage;
use App\Models\ClientArcAssociation;
/**
 * Class TikTokAdGroupsImporter
 */
class TikTokAdGroupsImporter extends BaseImporter
{

    public $send_created_event = false;

    public function doImport(ReportLogbook $request)
    {
        $identifier = $request->identifier;
        $table = $this->getReportTableName($request);

        Log::info("[TikTokAdGroupsImporter] Start importing for identifier {$identifier}");

        try {
            $localReport = $request->infoOriginalLocalReport;

            $response = json_decode(Storage::disk('system')->get($localReport));
            
            $data = $response->response->data->list ?? [];
            $totalItems = 0;
            if (!empty($data)) {
                $totalItems = count($data) ?? 0;
            }

            // Check Data size
            if ($totalItems < 1) { // Note: depends from source
                Log::info("[TikTokAdGroupsImporter] Empty data on {$localReport}");
                return false;
            }

            // Clear data BEFORE importing new data
            $this->deleteData($request);

            $validAssociations = ClientArcAssociation::where('source', $this->source)
                ->inPeriod($request->date_end)
                ->get();

            $activeAssoc = null;
            foreach ($validAssociations as $assoc) {
                if ($assoc->info['advertiser_id'] == $request->identifier) {
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
                    $x = new TikTokAdGroupsReportData();
                    $x->setTable($table)->upsert(
                        $toInsert,
                        $x->getPrimaryKey(),
                        $x->getUpdatable()
                    );
                });

            return $totalItems;
        } catch (Exception $e) {
            // In case of exception, set update failed and rollBack DB
            Log::error('[TikTokAdGroupsImporter] ' . $e->getMessage());
            throw $e;
        }
    }

    private function processRecord($row, ReportLogbook $request, $activeAssoc)
    {

        $date = $request->date_end;

        $timestamp = Carbon::now();


        $record = [
            'date'                  => $date,
            'identifier'            => $request->identifier,

            'client_id'             => $activeAssoc->client_id,
            'market_id'             => $activeAssoc->market_id,
            'market'                => $activeAssoc->market->code,

            'hash'                  => TikTokAdGroupsReportData::getHash([
                $row->campaign_id, $row->adgroup_id, $row->advertiser_id
            ]),

            'attributes' => json_encode($row),


            'created_at' => $timestamp,
            'updated_at' => $timestamp
        ];


        return $record;
    }
}
