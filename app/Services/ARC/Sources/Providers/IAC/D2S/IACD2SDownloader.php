<?php

namespace App\Services\ARC\Sources\Providers\IAC\D2S;

use Illuminate\Support\Facades\Storage;

use App\Exceptions\ARC\ReportException;
use App\Services\ARC\File\ReportUtils;
use App\Services\ARC\Sources\Abstracts\BaseDownloader;
use App\Models\ARC\ReportLogbook;
use Illuminate\Support\Facades\Log;
use PhpImap\Mailbox;
use PhpImap\Exceptions\ConnectionException;
use League\Csv\Statement;
use League\Csv\Reader;
use Carbon\Carbon;
/**
 * Class IACD2SDownloader
 */
class IACD2SDownloader extends BaseDownloader
{
    public function doDownload(ReportLogbook $request): bool
    {
        $identifier = $request->identifier;

        $server = config('arc.sources.iac.email_credentials.server');
        $username = config('arc.sources.iac.email_credentials.username');
        $password = config('arc.sources.iac.email_credentials.password');
        $protocol = config('arc.sources.iac.email_credentials.protocol');
        $port = config('arc.sources.iac.email_credentials.port');
        $tmp_dir = config('arc.tmp_path') . 'mail_attch';

        $reportFile = ReportUtils::suggestOriginalLocalReportFullPath($request, true, 'csv');

        try {
            $dsn = '{' . $server . '/imap/ssl}';
            $mailbox = new Mailbox(
                $dsn, // IMAP server and mailbox folder
                $username, // Username for the before configured mailbox
                $password, // Password for the before configured username
                $tmp_dir, // Directory, where attachments will be saved (optional)
                'UTF-8' // Server encoding (optional)
            );


            // set some connection arguments (if appropriate)
            //  $mailbox->setConnectionArgs(
            //      OP_SECURE | OP_DEBUG
            //  );

            $mailsIds = $mailbox->searchMailbox('UNSEEN SUBJECT "Daily Agency Report (Adsense)"');

            if (!$mailsIds) {
                Log::warning('[IACD2SDownloader] Empty mailbox');
                $mailbox->disconnect();
                return false;
            }

            foreach ($mailsIds as $mailId) {
                // Get all emails (messages)
                // PHP.net imap_search criteria: http://php.net/manual/en/function.imap-search.php
                $email = $mailbox->getMail(
                    $mailId, // ID of the email, you want to get
                    false // Do NOT mark emails as seen (optional)
                );
                
                if ((string) $email->fromAddress != 'noreply@lookermail.com') {
                    //it's not the original one
                    $mailbox->markMailAsRead($mailId);
                    continue;
                }
                if (!$email->hasAttachments()) {
                    //it's not the original one
                    Log::warning('[IACD2SDownloader] Mail With No Attachments (setting as read): ' . json_encode([
                        'mailId' => $mailId,
                        'date' => (string) $email->date,
                        'to' => (string) $email->toString,
                        'message_id' => (string) $email->messageId,
                    ]));
                    $mailbox->markMailAsRead($mailId);
                    continue;
                }
    
                if (!empty($email->autoSubmitted)) {
                    // Mark email as "read" / "seen"
                    $mailbox->markMailAsRead($mailId);
                    continue;
                }
    
                if (!empty($email->precedence)) {
                    // Mark email as "read" / "seen"
                    $mailbox->markMailAsRead($mailId);
                    continue;
                }


                $attachments = $email->getAttachments();

                foreach ($attachments as $attachment) {


                    // Set individually filePath for each single attachment
                    // In this case, every file will get the current Unix timestamp
                    $attachment->setFilePath($reportFile);

                    if ($attachment->saveToDisk()) {
                        $request->infoOriginalLocalReport = $reportFile;
                        $mailbox->markMailAsRead($mailId);                        
                    } else {
                        throw new ReportException("[IACD2SDownloader] Could not save " . (string)$attachment->name . '"', $request->source, $request->date_end);
                        $mailbox->disconnect();
                        return false;
                    }
                    continue;
                }
                
                $emailDate = Carbon::parse((string) $email->date);
                $minDate = null;
                $maxDate = null;
                // Set read file and set header
                $csv = Reader::createFromPath($reportFile, 'r');
                //$csv->setHeaderOffset(1);
    
                $stmt = (new Statement())->offset(1)->limit($csv->count() - 2);
                $csvRows = $stmt->process($csv);

                foreach ($csvRows as $rowArr) {
                    $row = (object) [
                        'date' => $rowArr[1] ?? '',
                    ];
    
                    if (empty($row->date)) continue;
                    if (is_null($minDate) || $minDate > $row->date) {
                        $minDate = $row->date;
                    }


                    if ((is_null($maxDate) || $maxDate < $row->date) && ($row->date != $emailDate->format('Y-m-d'))) {
                        $maxDate = $row->date;
                    }
                }

                $request->date_end = $maxDate;
                $request->date_begin = $minDate;

                //check if a request like this already exists
                $r = ReportLogbook::where('source', $request->source)
                    ->where('report_type', $request->report_type)
                    ->where('date_end', $maxDate)
                    ->where('date_begin', $minDate)
                    ->where('identifier', $request->identifier)->first();
                
                if(!is_null($r) && $r->id != $request->id) {
                    Log::info('[IACD2SDownloader] Clearing Existing Logbook');
                    $r->delete();
                }

                Log::info('[IACD2SDownloader] Email Saved: ' . json_encode([
                    'mailId' => $mailId,
                    'date' => (string) $email->date,
                    'to' => (string) $email->toString,
                    'message_id' => (string) $email->messageId,
                ]));
                //$mailbox->markMailAsRead($mailId);
                $mailbox->disconnect();
                return true;
            }
        } catch (ConnectionException $ex) {
            throw new ReportException("[IACD2SDownloader]: " . $ex->getMessage(), $request->source, $request->date_end);
            $mailbox->disconnect();
            return false;
        } catch (\Exception $ex) {
            throw new ReportException("[IACD2SDownloader]: " . $ex->getMessage(),  $request->source, $request->date_end);
            $mailbox->disconnect();
            return false;
        }

        return true;
    }
}
