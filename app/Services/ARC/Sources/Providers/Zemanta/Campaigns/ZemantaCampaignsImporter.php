<?php

namespace App\Services\ARC\Sources\Providers\Zemanta\Campaigns;

use App\Services\ARC\Sources\Abstracts\BaseImporter;
use App\Models\CurrencyConversion;
use App\Models\ARC\ReportLogbook;
use App\Models\ARC\Providers\Zemanta\ZemantaCampaignsReportData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use League\Csv\Statement;
use League\Csv\Reader;

use Exception;
use Illuminate\Support\Carbon as Carbon;
use Illuminate\Support\Facades\Storage as Storage;
use App\Models\ClientArcAssociation;

/**
 * Class ZemantaCampaignsImporter
 */
class ZemantaCampaignsImporter extends BaseImporter
{

    public $send_created_event = false;

    public function doImport(ReportLogbook $request)
    {
        $identifier = $request->identifier;
        $table = $this->getReportTableName($request);

        Log::info("[ZemantaCampaignsImporter] Start importing for identifier {$identifier}");

        try {
            $count = 0;
            // Read the CSV
            $localReport = $request->infoOriginalLocalReport;

            // Set read file and set header
            $json = json_decode(Storage::disk('system')->get($localReport));

            // Check CSV size
            if (empty($json)) { // Note: depends from source
                Log::info("[ZemantaCampaignsImporter] Empty data on {$localReport}");
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

            collect($json)
            ->chunk($this->insert_chunk_size)
            ->each(function ($chunkDataRows) use ($request, $table, $identifier, $activeAssoc, &$count) {
                $toInsert = [];
                foreach ($chunkDataRows as $dataRow) {
                    $record = $this->processRecord($dataRow, $request, $activeAssoc);
                    $toInsert[] = $record;
                    $count++;
                }
                // Can we use also a factory method
                $x = new ZemantaCampaignsReportData();
                $x->setTable($table)->upsert(
                    $toInsert,
                    $x->getPrimaryKey(),
                    $x->getUpdatable()
                );
            });

            return $count;
        } catch (Exception $e) {
            // In case of exception, set update failed and rollBack DB
            throw $e;
        }
    }

    private function processRecord($row, ReportLogbook $request, ClientArcAssociation $activeAssoc)
    {
        $date = $request->date_end;

        $timestamp = Carbon::now();


        $record = [
            'date'                  => $date,
            'identifier'            => $request->identifier,

            'client_id'             => $activeAssoc->client_id,
            'market_id'             => $activeAssoc->market_id,
            'market'                => $activeAssoc->market->code,

            'campaign_id'           => $row->id,
            'data'                  => json_encode($row),


            'created_at' => $timestamp,
            'updated_at' => $timestamp
        ];


        return $record;
    }
}
