<?php

namespace App\Services\ARC\Sources\Providers\System1\Subid;

use Illuminate\Support\Facades\Storage;

use App\Exceptions\ARC\ReportException;
use App\Services\ARC\File\ReportUtils;
use App\Services\ARC\Sources\Abstracts\BaseDownloader;
use App\Models\ARC\ReportLogbook;
use Illuminate\Support\Facades\Log;

use App\Services\ARC\Sources\Providers\System1\System1Library;
use App\Models\ClientArcAssociation;
/**
 * Class System1SubidDownloader
 */
class System1SubidDownloader extends BaseDownloader
{
    public function doDownload(ReportLogbook $request): bool
    {
        $site = $request->identifier;
        

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

        if(is_null($activeAssoc)) {
            $err = '[System1SubidDownloader] Unable to find a valid associations for ' . $assoc->info['domain'] . ' on ' . $request->date;
            Log::error($err);
            throw new ReportException($err, $request->source, $request->date_end);
            return false;
        }

        $info = (object) $activeAssoc->info;


        $lib = new System1Library($info->api_key);

        $reportFile = ReportUtils::suggestOriginalLocalReportFullPath($request);

        Log::debug("[System1SubidDownloader] Try to download System1Subid report for PartnerID = {$site}, {$request->date_end}");


        try {
        $dataAvailable = $lib->isDataAvailable($request->date_begin);

        if(empty($dataAvailable)) {
            Log::warning('[System1SubidDownloader][DATA_UNAVAILABLE] '. $request->date_begin);
            return false;
        }

        $dataResults = $lib->downloadSubIdReport($request->date_begin);

        if ($dataResults['status'] == false) {
            if(!empty($dataResults['message'])) {
                Log::warning('[System1SubidDownloader][ERROR] '. $dataResults['message']);
            }
            return false;
        } else {
            // Save File
            Storage::disk('system')->put($reportFile, json_encode($dataResults['data']));
            $request->infoOriginalLocalReport = $reportFile;
        }
        return true;
        }
        catch(\Exception $e) {
            throw $e;
        }
    }
}
