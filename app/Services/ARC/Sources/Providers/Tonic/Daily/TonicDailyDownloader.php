<?php

namespace App\Services\ARC\Sources\Providers\Tonic\Daily;

use Illuminate\Support\Facades\Storage;

use App\Exceptions\ARC\ReportException;
use App\Services\ARC\File\ReportUtils;
use App\Services\ARC\Sources\Abstracts\BaseDownloader;
use App\Models\ARC\ReportLogbook;
use Illuminate\Support\Facades\Log;

use App\Services\ARC\Sources\Providers\Tonic\TonicLibrary;
use App\Models\ClientArcAssociation;
/**
 * Class TonicDailyDownloader
 */
class TonicDailyDownloader extends BaseDownloader
{
    public function doDownload(ReportLogbook $request): bool
    {
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

        if(is_null($activeAssoc)) {
            $err = '[TonicSubidDownloader] Unable to find a valid associations for ' . $request->identifier . ' on ' . $request->date;
            Log::error($err);
            throw new ReportException($err, $request->source, $request->date_end);
            return false;
        }
        $info = $activeAssoc->info;

        $lib = new TonicLibrary($info['key'], $info['secret']);

        if(!$lib->login()) {
            $err = '[TonicSubidDownloader] Unable to login for ' . $info['key'] . ' on ' . $request->date;
            Log::error($err);
            throw new ReportException($err, $request->source, $request->date_end);
            return false;
        }


        $data = $lib->getReport($request->date_end);
        

        if(!empty($data['status'])) {
            $reportFile = ReportUtils::suggestOriginalLocalReportFullPath($request, true);
            $jsonData = json_encode($data['response']);
			Storage::disk('system')->put($reportFile, $jsonData);
        } else {
            $err = $data['error'] ?? 'Unknown error';
            Log::error($err);
            throw new ReportException($err, $request->source, $request->date_end);
            return false;
        }

        $request->infoOriginalLocalReport = $reportFile;
        return true;
    }
}
