<?php

namespace App\Services\ARC\Sources\Abstracts;

use App\Events\ReportTableCreatedEvent;
use App\Exceptions\ARC\ReportException;
use App\Services\ARC\Database\Factory;
use App\Services\ARC\File\ReportUtils;
use App\Services\ARC\Sources\Abstracts\BasePhase;
use App\Models\ARC\ReportLogbook;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

abstract class BaseImporter extends BasePhase
{

    public $insert_chunk_size = 10000; //default value
    public $export_pack_limit = 5000;

    public $enabledExportCompression = true;

    protected $siteAssociation;

    protected $entity_separator = '||'; // double pipe

    protected $source_mapper = [
    ];


    abstract public function doImport(ReportLogbook $request);

    public function getInSourceMap($source)
    {
        $source = strtolower($source);
        if(isset($this->source_mapper[$source])) {
            return ucwords($this->source_mapper[$source]);
        }

        return ucwords($source);
    }

    public function makeTableIfNotExists(ReportLogbook $request)
    {
        $tableName = strtolower($this->getReportTableName($request));

        /* If table exists */
        if (Schema::hasTable($tableName)) {
            return false;
        }

        Schema::create($tableName, function (Blueprint $table) use ($request, $tableName) {
            // Magic of pass-by-reference object :)
            $this->buildReportTableModel($request, $table, $tableName);
        });


        return true;
    }

    public function copyOriginalS3ReportToLocal(ReportLogbook $request)
    {
        return ReportUtils::copyOriginalS3ReportToLocal($request);
    }

    public function copyProcessedLocalReportToS3($processedLocalReport, ReportLogbook $request, $date = null)
    {
        return ReportUtils::copyProcessedLocalReportToS3($processedLocalReport, $request, $date);
    }

    public function getReportTableName(ReportLogbook $request)
    {
        $x = Factory::getReportModel($request);
        return $x->getReportTableName($request);
    }

    public function buildReportTableModel(ReportLogbook $request, Blueprint $table, string $tableName)
    {
        $x = Factory::getReportModel($request);
        return $x->buildReportTableModel($table, $tableName);
    }

    protected function deleteData(ReportLogbook $request)
    {
        $x = Factory::getReportModel($request);
        $table = $x->getReportTableName($request);

        if (Schema::hasTable($table)) {
            $x->deleteData($request);
        } else {
            Log::warning("[BaseImporter][deleteData] Table {$table} does not exists!");
        }
        return true;
    }


    /**
     * Added date field in order to make an export
     * 
     */
    public function doExport(ReportLogbook $request, $date = null)
    {
        // Fields to remove from export:
        $filteredFields = ['created_at', 'updated_at'];
        $msg = "[BaseImporter] Start export for {$request->source} {$request->report_type} {$request->identifier}";
  
        if($date !== null) {
            $msg .= " {$date}";
            $rq = clone $request;
            $rq->date_end = $rq->date_begin = $date;
            $x = Factory::getReportModel($rq);
        } else {
            $msg .= " {$request->date_end}";
            $x = Factory::getReportModel($request);
        }

        Log::info($msg);

        $x = Factory::getReportModel($request);

        $offset = 0;
        $limit = $this->export_pack_limit;

        $fn = ReportUtils::suggestProcessedLocalReportFullPath($request, true, $date);

        if(file_exists($fn)) {
            unlink($fn);
        }

        if($date !== null) {
            $rows = $x->data($date, $request->identifier)
                    ->offset($offset)->limit($limit)->get();
        } else {
            $rows = $x->data($request->date_end, $request->identifier)
                    ->offset($offset)->limit($limit)->get();
        }

        if ($rows->count() == 0) {
            Log::debug("[BaseImporter][".$request->source."//".$request->report_type."] No rows to export.");
            return;
        }

        $packed = 0;
        while($rows->count() > 0) {
            $packed += $rows->count();
            Log::debug("[BaseImporter][".$request->source."//".$request->report_type."] Packed ". $packed . ' rows...');
            for ($idx = 0; $idx < count($rows); $idx++) {
                $row = $rows[$idx];
                $data = $row->toArray();
                unset($data['created_at']);
                unset($data['updated_at']);
    
                $str = implode('', [
                    json_encode($data),
                    ($idx == count($rows) -1) ? '' : "\n",
                ]);
                file_put_contents(
                    $fn,
                    $str,
                    FILE_APPEND
                );
            }
            $offset += $limit;
            if($date !== null) {
                $rows = $x->data($date, $request->identifier)
                        ->offset($offset)->limit($limit)->get();
            } else {
                $rows = $x->data($request->date_end, $request->identifier)
                        ->offset($offset)->limit($limit)->get();
            }
        }



        if ($this->enabledExportCompression) {
            $gzipped = ReportUtils::gzCompressFile($fn);
            $fn = $gzipped;
            Log::debug("[BaseImporter][".$request->source."//".$request->report_type."] Compressed Processed File ...");
        }

        return $fn;       
    }

    protected function retriveDateRequestAndTable($date, ReportLogbook $request)
    {
        $rq = clone $request;
        $rq->date_end = $rq->date_begin = $date;

        $this->makeTableIfNotExists($rq);

        $tbl = $this->getReportTableName($rq);

        return [$rq, $tbl];
    }

    // protected function buildInsertSql($table, $fillable, $toInsert) {
        

    //     $sql = 'INSERT INTO ' . $table .'('. implode(',', $fillable) .') VALUES (';

    //     foreach($toInsert as $idx => $el)
    //     {
    //         foreach($el as $key => $value) {
    //             $el[$key] = DB::getPdo()->quote($value);
    //         }
    //         $toInsert[$idx] = implode(',', $el);
    //     }

    //     $sql .= implode('), (', $toInsert) .');';

    //     return $sql;
    // }

    protected function insert($table,  $toInsert) {
        if(empty($toInsert)) return false;
        $fillable = array_keys($toInsert[0]);
        $sql = 'INSERT INTO ' . $table .'('. implode(',', $fillable) .') VALUES (';

        foreach($toInsert as $idx => $el)
        {
            foreach($el as $key => $value) {
                if(is_null($value)) {
                    $el[$key] = 'NULL';
                } else {
                    $el[$key] = DB::getPdo()->quote($value);
                }
                
            }
            $toInsert[$idx] = implode(',', $el);
        }

        $sql .= implode('), (', $toInsert) .');';
        return DB::statement($sql);
    }

}
