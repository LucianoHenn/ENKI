<?php

namespace App\Services;

use Google\Client;
use Google\Service\Drive;

class GoogleDrive
{

    protected $client;
    protected $service;

    public function __construct()
    {
        $this->client = new Client();
        $this->client->setApplicationName(config('app.name'));
        $this->client->setAuthConfig(config_path() . '/google-drive.credentials.json');
        $this->client->addScope(Drive::DRIVE);

        $this->service = new Drive($this->client);
    }

    public function getFileMeta($fileId)
    {
        try {
            return $this->service->files->get($fileId);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function createFolder($folderName, $parentFolderId = '')
    {
        try {
            $meta = [
                'name' => $folderName,
                'mimeType' => 'application/vnd.google-apps.folder'
            ];
            if (!empty($parentFolderId)) {
                $meta['parents'] = [$parentFolderId];
            }
            $fileMetadata = new Drive\DriveFile($meta);
            $file = $this->service->files->create($fileMetadata, array(
                'fields' => 'id'
            ));

            return $file->id;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function uploadFileToFolder($fileName, $fileContent, $fileMimeType, $folderId = '')
    {
        try {
            $meta = [
                'name' => $fileName,
            ];
            if (!empty($folderId)) {
                $meta['parents'] = [$folderId];
            }
            $fileMetadata = new Drive\DriveFile($meta);

            $file = $this->service->files->create($fileMetadata, array(
                'data' => $fileContent,
                'mimeType' => $fileMimeType,
                'uploadType' => 'multipart',
                'fields' => 'id'
            ));

            return $file->id;
        } catch (\Exception $e) {

            return false;
        }
    }

    public function getFolderContent($folderId)
    {
        try {
            $optParams = array(
                'pageSize' => 10,
                'fields' => "nextPageToken, files(contentHints/thumbnail,fileExtension,iconLink,id,name,size,thumbnailLink,webContentLink,webViewLink,mimeType,parents)",
                'q' => "'" . $folderId . "' in parents"
            );
            $results = $this->service->files->listFiles($optParams);

            $tmp = [
                'folders' => [],
                'files' => []
            ];
            do {
                foreach ($results['files'] as $el) {
                    $type = 'files';
                    if ($el->mimeType == 'application/vnd.google-apps.folder') {
                        $type = 'folders';
                    }

                    $tmp[$type][] = [
                        'id' => $el->id,
                        'name' => $el->name,
                        'size' => $el->size,
                        'mimeType' => $el->mimeType,
                        'webViewLink' => $el->webViewLink
                    ];
                }

                if (!empty($results->nextPageToken)) {
                    $optParams['pageToken'] = $results->nextPageToken;
                    $results = $this->service->files->listFiles($optParams);
                } else {
                    $results = null;
                }
            } while (!empty($results));

            return $tmp;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getFolderImages($folderId)
    {
        try {
            $optParams = array(
                'pageSize' => 10,
                'fields' => "nextPageToken, files(contentHints/thumbnail,fileExtension,iconLink,id,name,size,thumbnailLink,webContentLink,webViewLink,mimeType,parents)",
                'q' => "'" . $folderId . "' in parents"
            );
            $results = $this->service->files->listFiles($optParams);

            $tmp = [
                'files' => []
            ];
            do {
                foreach ($results['files'] as $el) {
                    if ($el->mimeType !== 'application/vnd.google-apps.folder' && ($el->mimeType === 'image/png' || $el->mimeType === 'image/jpg' || $el->mimeType === 'image/jpeg')) {
                        $tmp['files'][] = [
                            'id' => $el->id,
                            'name' => $el->name,
                            'size' => $el->size,
                            'mimeType' => $el->mimeType,
                            'webViewLink' => $el->webViewLink
                        ];
                    }
                }

                if (!empty($results->nextPageToken)) {
                    $optParams['pageToken'] = $results->nextPageToken;
                    $results = $this->service->files->listFiles($optParams);
                } else {
                    $results = null;
                }
            } while (!empty($results));

            return $tmp;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function downloadFile($fileId)
    {
        try {

            $response = $this->service->files->get($fileId, array(
                'alt' => 'media'
            ));
            $content = $response->getBody()->getContents();
            return $content;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function isReadable($fileId)
    {
        try {
            $x = $this->service->files->get($fileId, ['fields' => 'mimeType,capabilities']);

            if ($x->mimeType != 'application/vnd.google-apps.folder') {
                return $x->capabilities->canCopy && $x->capabilities->canDownload;
            } else {
                return $x->capabilities->canListChildren;
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function isWritable($fileId)
    {
        try {
            $x = $this->service->files->get($fileId, ['fields' => 'mimeType,capabilities']);

            if ($x->mimeType != 'application/vnd.google-apps.folder') {
                return $x->capabilities->canCopy && $x->capabilities->canDownload && $x->capabilities->canEdit;
            } else {
                return $x->capabilities->canListChildren && $x->capabilities->canAddChildren && $x->capabilities->canRemoveChildren;
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function getGoogleClientEmail()
    {
        $config = json_decode(file_get_contents(config_path() . '/google-drive.credentials.json'));
        return $config->client_email;
    }

    public function getFileIdFromFolderUrl($folderUrl)
    {
        // Use parse_url to parse the URL and get the 'path' part
        $path = parse_url($folderUrl, PHP_URL_PATH);
        // Split the path by '/' and get the part after the third '/' (the fileId should be after the third '/')
        $parts = explode('/', $path);
        if (count($parts) < 4) {
            return null;
        }
        $fileId = $parts[3];
        // Split the fileId by '?' and get the first element (before the '?')
        $parts = explode('?', $fileId);
        $fileId = $parts[0];
        return $fileId;
    }
}
