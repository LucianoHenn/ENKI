<?php

namespace App\Services\ARC\File;

use App\Models\ARC\ReportLogbook;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use ZipArchive;

class ReportUtils
{
    public static function getS3EnvFolder()
    {
        $env = App::environment();
        if ($env !== 'production') {
            return 'develop';
        }
        return $env; // --> production
    }

    public static function getLocalReportPath()
    {
        return config('arc.local_reports_path');
    }

    public static function suggestOriginalReportFilename(ReportLogbook $request, $date = null, $extension = 'json')
    {
        // RULE:
        // <request date_end>_<request identifier>_<market>.<extension>

        if($date !== null) {
            $dt = date('Ymd', strtotime($date));
        } else {
            $dt = date('Ymd', strtotime($request->date_end));
        }

        $tokens = [
            $dt,
            $request->identifier,
            strtolower($request->market == '*' ? 'all' : $request->market),
        ];
        // Remove empty tokens, like market
        $tokens = array_filter($tokens);

        $fn = implode('_', $tokens) . '.' . $extension;

        return $fn;
    }

    public static function suggestProcessedReportFilename(ReportLogbook $request, $date = null)
    {
        return 'p_' . self::suggestOriginalReportFilename($request, $date);
    }

    public static function createParentFolder(string $fullpath)
    {
        // Normalize doubled slashes
        $fullpath = dirname($fullpath);
        return Storage::disk('system')->makeDirectory($fullpath);
    }

    public static function suggestOriginalLocalReportFullPath(ReportLogbook $request, $createPathIfNotExists = true, $extension = 'json')
    {
        // RULE:
        // <base folder for storage>/<source in lowercase>/<report_type in lowercase>/<request date_end>_<request identifier>_<creation date report YmdHis>.<ext>
        $fp = implode('/', array_filter([
            config('arc.local_reports_path'),
            'original',
            strtolower($request->source),
            strtolower($request->report_type),
            self::suggestOriginalReportFilename($request, null, $extension)
        ]));

        if ($createPathIfNotExists) {
            self::createParentFolder($fp);
        }

        return $fp;
    }

    public static function getTemporaryS3Url($filePath, $expire_in_minutes = 30) {
        return Storage::disk('enkiReports')->temporaryUrl($filePath, now()->addMinutes($expire_in_minutes));
    }
    

    public static function getLocalFileSize($fName)
    {
        return Storage::disk('system')->size($fName);
    }

    public static function suggestProcessedLocalReportFullPath(ReportLogbook $request, $createPathIfNotExists = true , $date = null)
    {
        // RULE:
        // <base folder for storage>/<source in lowercase>/<request date_end>_<request identifier>_<creation date report YmdHis>.<ext>

        $fp = implode('/', [
            config('arc.local_reports_path'),
            'processed',
            strtolower($request->source),
            strtolower($request->report_type),
            self::suggestProcessedReportFilename($request, $date)
        ]);

        if ($createPathIfNotExists) {
            self::createParentFolder($fp);
        }

        return $fp;
    }

    public static function copyOriginalLocalReportToS3(ReportLogbook $request, $date = null)
    {
        $localReport = $request->infoOriginalLocalReport;
        if (empty($localReport)) {
            return false;
        }

        $envPath = self::getS3EnvFolder();

        $rpDate = $date ?? $request->date_end;


        $path = implode('/', [
            $envPath,
            'original',
            strtolower($request->family),
            strtolower($request->report_type),
            'dt=' . $rpDate,
            basename($localReport)
        ]);

        \Log::debug("copyOriginalLocalReportToS3: copy {$localReport} to {$path}");
        Storage::disk('enkiReports')->put($path, fopen($localReport, 'r'));

        $request->downloaded_path = $path;
        return true;
    }


    public static function deleteLocalFile($fn) {
        return Storage::disk('system')->delete($fn);
    }

    public static function copyProcessedLocalReportToS3($processedLocalReport, ReportLogbook $request, $date = null)
    {
        //We Avoid to put on S3 empty files.
        //Athena does not like it for parsing
        clearstatcache();
        if (!filesize($processedLocalReport)) {
            return false;
        }

        $envPath = self::getS3EnvFolder();

        $rpDate = $date ?? $request->date_end;

        $path = implode('/', [
            $envPath,
            'processed',
            strtolower($request->family),
            strtolower($request->report_type),
            'dt=' . $rpDate,
            basename($processedLocalReport)
        ]);

        \Log::debug("copyProcessedLocalReportToS3: copy {$processedLocalReport} to {$path}");
        if (self::copyFileToS3($path, $processedLocalReport)) {
            $tmp = $request->info ?? [];
            $tmp['processedRemoteReport'] = $path;
            $request->info = $tmp;
            return true;
        }
    }

    // Storage::put($directory . '/' . $imageName, 
    //         $image, [
    //           'visibility' => 'public',
    //           'Expires' => gmdate('D, d M Y H:i:s \G\M\T', time() + (60 * 60 * 24 * 7)),
    //           'CacheControl' => 'max-age=315360000, no-transform, public',
    //     ]);
    public static function copyFileToS3($remoteFileFullPath, $localFileFullPath, $options = [])
    {
        return Storage::disk('enkiReports')->put($remoteFileFullPath, fopen($localFileFullPath, 'r'), $options);

    }

    public static function copyOriginalS3ReportToLocal(ReportLogbook $request)
    {
        $remoteFile = $request->downloaded_path;
        if (empty($remoteFile)) {
            return false;
        }

        // Test if remote file exists
        if (!Storage::disk('enkiReports')->has($remoteFile)) {
            return false;
        }

        //$path = ReportUtils::getLocalReportPath();
        $file = $request->infoOriginalLocalReport;

        \Log::debug("copyOriginalS3ReportToLocal: copy {$remoteFile} to {$file}");
        Storage::disk('system')->makeDirectory(dirname($file));
        return Storage::disk('system')->put($file, Storage::disk('enkiReports')->get($remoteFile));
    }


    public static function zipCompressFile($source)
    {
        $dest = $source . '.zip';
        $zip = new ZipArchive;
        if ($zip->open($dest, ZipArchive::CREATE) === TRUE) {
            // Add files to the zip file
            $zip->addFile($source, basename($source));

            // All files are added, so close the zip file.
            $zip->close();
            return $dest;
        } else {
            return false;
        }
    }

    /**
     * GZIPs a file on disk (appending .gz to the name)
     *
     * Using the command line to avoid any problem on the size of the chunks
     * @param string $source Path to file that should be compressed
     * @param integer $level GZIP compression level (default: 9)
     * @return string New filename (with .gz appended) if success, or false if operation fails
     */
    public static function gzCompressFile($source, $level = 9)
    {
        $dest = $source . '.gz';
        \Log::debug("gzCompressFile: compress {$source} to level {$level} (argument ignored, used default value 9)");
        try {
            exec('gzip -f9 '.escapeshellarg($source), $output, $retval);
        }
        catch(\Exception $e)
        {
            \Log::warning("[gzCompressFile] ERROR: exec() failure - " . $e->getMessage() . " (code $retval - output '" . json_encode($output) . "')");
            return false;
        }
        if($retval != 0) {
            \Log::warning("[gzCompressFile] ERROR: gzip returned code $retval - output '" . json_encode($output) . "'");
            return false;
        }
        if(!file_exists($dest)) {
            \Log::warning("[gzCompressFile] ERROR: file $dest doesn't exists (code $retval - output '" . json_encode($output) . "')");
            return false;
        }
        //let's test the file integrity
        try {
            exec('gzip -t ' . escapeshellarg($dest), $output, $retval);
        }
        catch(\Exception $e)
        {
            \Log::warning("[gzCompressFile] ERROR: exec() failure - " . $e->getMessage());
            return false;
        }
        if($retval != 0) {
            \Log::warning("[gzCompressFile] ERROR: gzip returned code $retval - output '" . json_encode($output) . "'");
            return false;
        }
        \Log::debug("gzCompressFile: compressed {$source} successfully");
        Storage::disk('system')->delete($source);
        return $dest;
    }
}
