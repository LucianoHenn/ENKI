<?php


namespace App\Services\ARC\Sources\Providers\Yahoo;

use Illuminate\Support\Facades\Log;
use Firebase\JWT\JWT;

use GuzzleHttp\Client;
use Storage;

class YahooLibrary
{

    protected $api_url;
    protected $api_login_url;
    protected $client_id;
    protected $client_secret;

    protected $access_token;


    protected $jwt_body        = array(
        'aud' => '', //the api client login url
        'iss' => '', //the api client
        'sub' => '', //the api client
        'exp' => '0', //expire time in seconds
        'iat' => '0', //issued time in seconds
    );

    protected static $markets = array(
        'au' => false,
        'br' => false,
        'ca' => true,
        'de' => true,
        'dk' => false,
        'es' => true,
        'fr' => true,
        'it' => true,
        'mx' => false,
        'nl' => false,
        'se' => false,
        'uk' => true,
        'us' => true,
        'in' => false,
        'fi' => false,
        'no' => false
    );


    protected $tmp_path;

    public function __construct()
    {
        $this->api_url         = config('arc.sources.yahoo.api_url');
        $this->api_login_url   = config('arc.sources.yahoo.api_login_url');
        $this->client_id       = config('arc.sources.yahoo.api_client_id');
        $this->client_secret   = config('arc.sources.yahoo.api_client_secret');
        $this->tmp_path        = config('arc.tmp_path');
    }


    public static function getMarkets()
    {
        return array_keys(static::$markets);
    }

    public static function getActiveMarkets()
    {
        return array_keys(array_filter(static::$markets, function ($v, $k) {
            return !empty($v);
        }, ARRAY_FILTER_USE_BOTH));
    }

    public static function market2countries($market)
    {
        $tr = array(
            'au' => 'Australia',
            'br' => 'Brazil',
            'ca' => 'Canada',
            'de' => 'Germany',
            'dk' => 'Denmark',
            'es' => 'Spain',
            'fi' => 'Finland',
            'fr' => 'France',
            'in' => 'India',
            'it' => 'Italy',
            'mx' => 'Mexico',
            'nl' => 'Netherlands',
            'no' => 'Norway',
            'se' => 'Sweden',
            'uk' => 'United Kingdom',
            'us' => 'United States'
        );
        if (!empty($tr[$market])) return $tr[$market];
        else return false;
    }

    public static function country2market($country)
    {
        $tr = array(
            'Argentina'         => '',
            'Australia'         => 'au',
            'Austria'           => '',
            'Brazil'            => 'br',
            'Canada'            => 'ca',
            'China'             => '',
            'Denmark'           => 'dk',
            'Finland'           => 'fi',
            'France'            => 'fr',
            'Germany'           => 'de',
            'Hong Kong'         => '',
            'India'             => 'in',
            'Italy'             => 'it',
            'Japan'             => '',
            'Korea'             => '',
            'Mexico'            => 'mx',
            'Netherlands'       => 'nl',
            'Norway'            => 'no',
            'Singapore'         => '',
            'Spain'             => 'es',
            'Sweden'            => 'se',
            'Switzerland'       => '',
            'Taiwan'            => '',
            'United Kingdom'    => 'uk',
            'United States'     => 'us'
        );
        if (!empty($tr[$country])) return $tr[$country];
        else return false;
    }


    #region JWT Functions
    protected function getJWTBody()
    {
        $ts = time();
        $this->_jwt_body['aud'] = $this->api_login_url . '?realm=pi';
        $this->_jwt_body['iss'] = $this->client_id;
        $this->_jwt_body['sub'] = $this->client_id;
        $this->_jwt_body['exp'] = $ts + 600; // 600 is suggested by yahoo
        $this->_jwt_body['iat'] = $ts; //currentTime right now

        return $this->_jwt_body;
    }

    protected function getJWTSignature()
    {
        
        $body = $this->getJWTBody();
        $signature = JWT::encode($body, $this->client_secret, 'HS256');
        return $signature;
    }
    #endregion JWT Functions

    public function getAccessToken()
    {
        $client = new Client([
            'verify' => false,
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Accept' => 'application/json'
            ]
        ]);


        try {
            
            //Log::info('[YahooLibrary][getAccessToken] EndPoint: ' . $this->api_login_url);
            $response = $client->request('POST', $this->api_login_url, [
                'form_params' => [
                    'grant_type'    => 'client_credentials',
                    'scope'         => 'pi-api-access',
                    'realm'         => 'pi',
                    'client_assertion_type' => 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer',
                    'client_assertion'      => $this->getJWTSignature()
                ]
            ]);
            $this->access_token = json_decode($response->getBody());


            return !empty($this->access_token);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            
            Log::warning(json_encode([
                'log' => '[ARC][YahooLibrary][getAccessToken]',
                'log_type' => 'warning',
                'status' => false,
                'exception' => '\GuzzleHttp\Exception\ClientException',
                'message' => $e->getResponse()->getReasonPhrase()
            ]));
            return false;
        } catch (\Exception $e) {
            
            Log::warning(json_encode([
                'log' => '[ARC][YahooLibrary][getAccessToken]',
                'log_type' => 'warning',
                'status' => false,
                'exception' => get_class($e),
                'message' => $e->getMessage()
            ]));
            return false;
        }
        
        return false;
    }

    protected function get($url)
    {
        if (empty($this->access_token)) {
            if (!$this->getAccessToken()) {
                return (object) [
                    'status' => false,
                    'error' => 'Empty Token',
                    'message' => 'Unable to Retrieve Token'
                ];
            }
        }

        $req = curl_init();
        curl_setopt($req, CURLOPT_VERBOSE, 0);
        curl_setopt($req, CURLOPT_HEADER, 0);
        curl_setopt($req, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($req, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($req, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($req, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . $this->access_token->access_token
        ));
        curl_setopt($req, CURLOPT_URL, $url);
        curl_setopt($req, CURLOPT_POST, 0);
        curl_setopt($req, CURLOPT_TIMEOUT, 60 * 15);
        curl_setopt($req, CURLOPT_CONNECTTIMEOUT, 60 * 15);

        $resp = curl_exec($req);
        $contentType = curl_getinfo($req, CURLINFO_CONTENT_TYPE);
        if (stripos($contentType, 'application/json') == FALSE && stripos($contentType, 'application\/json')/* && strtolower($contentType) != 'application/x-zip'*/) {
            $error = 'Wrong content type [' . $contentType . ']';
            Log::warning(json_encode([
                'log' => '[ARC][YahooLibrary][get]',
                'log_type' => 'warning',
                'status' => false,
                'exception' => 'Wrong content type',
                'message' => $error
            ]));
            curl_close($req);
            return (object)['status' => false, 'error' => 'Wrong Content Type', 'message' => $error];
        }
        curl_close($req);


        $resp = json_decode($resp);

        if ($resp === false) {
            $error = 'Error decoding json data';
            Log::warning(json_encode([
                'log' => '[ARC][YahooLibrary][get]',
                'log_type' => 'warning',
                'status' => false,
                'exception' => 'Error decoding json data',
                'message' => $error
            ]));
            return (object)['status' => false, 'error' => 'Error decoding json data', 'message' => $error];
        }

        return (object)[
            'status' => true,
            'data' => $resp
        ];
    }

    /**
     * $rollup : Daily / Hourly
     * $reportType : Search Type / Source
     * 
     * 
     **/
    public function getDataAvailability($rollup = 'Hourly', $reportType = 'Search Type', $dateBegin, $dateEnd = '')
    {

        if ($dateEnd == '') {
            $dateEnd = $dateBegin;
        }
        $dateBegin = str_replace('-', '', $dateBegin);
        $dateEnd   = str_replace('-', '', $dateEnd);
        $params = array(
            'startDate'     => $dateBegin,
            'endDate'       => $dateEnd,
            'format'        => 'json',
            'reportType'    => $reportType,
            'rollup'        => $rollup,
        );

        $url = $this->api_url . 'getDataAvailability?' . http_build_query($params);

        $availability = $this->get($url);
        //print_r($availability);
        if ($availability->status === false) {
            $availability->url = $url;
            return $availability;
        }
        if (isset($availability->data->ResultSet->Row->ERRORMSG)) {
            return (object)[
                'status' => false,
                'error' => $availability->data->ResultSet->Row->ERRORCODE,
                'message' => $availability->data->ResultSet->Row->ERRORMSG
            ];
        }

        $av =
            isset($availability->data->MetaInfo->ResponseStatus) &&
            strtolower($availability->data->MetaInfo->ResponseStatus) == 'success' &&
            isset($availability->data->ResultSet->Row->IS_AVAILABLE) &&
            strtolower($availability->data->ResultSet->Row->IS_AVAILABLE) == 'yes';
        if ($av === false) {
            return (object)[
                'status' => false,
                'error' => 'No Data Available',
                'message' => 'No Data Available',
                'info' => $availability->data->MetaInfo
            ];
        }
        return (object)[
            'status' => true,
            'data' => $availability->data->MetaInfo
        ];
    }

    public function scheduleSearchTypeHourlyReport($params)
    {
        if (isset($params['mrkt_id'])) {
            $market = self::market2countries($params['mrkt_id']);
            if ($market === false) {
                return (object) [
                    'status' => false,
                    'error' => 'Unknown market code',
                    'message' => 'Unknown market code "' . $params['mrkt_id'] . '"'
                ];
            }
            $params['mrkt_id'] = $market;
        }

        $av =  $this->getDataAvailability('Hourly', 'Search Type', $params['date_begin'], $params['date_end']);

        if ($av->status == false) {
            return $av;
        }

        $dateBegin = str_replace('-', '', $params['date_begin']);
        $dateEnd   = str_replace('-', '', $params['date_end']);
        $default = array(
            'userId'            => 'marcellov_bidberrymedia_pi',
            'attributeList'     => 'product,market,device_type,source tag,type tag,ad_type',
            'channel'           => '',
            'currency'          => 0,
            'dateRange'         => 'HOURLY',
            'dateRollup'        => 'Hourly',
            'device'            => 'ALL',
            'endDate'           => $dateEnd,
            'format'            => 'json',
            'mrkt_id'           => 'ALL',
            'orderBy'           => 'DATA+HOUR',
            'partnerList'       => 'ALL',
            'product'           => 'ALL',
            'returnRows'        => 300000,
            'scheEndDate'       => substr($dateEnd, 0, 8),
            'scheStartDate'     => substr($dateBegin, 0, 8),
            'scheduleFrequency' => 1,
            'sourceTag'         => '*',
            'startDate'         => $dateBegin,
            'startRow'          => 1,
            'type'              => 'ASYNC',
        );

        $params['label'] = str_replace(
            ' ',
            '_',
            strtolower(implode('__', [
                'Search Type',
                'Hourly',
                $params['mrkt_id'],
                $params['date_begin'],
                $params['date_end']
            ]))
        );
        $params = array_merge($default, $params);

        unset($params['date_begin']);
        unset($params['date_end']);

        $url = $this->api_url . 'getTypeDetailReport?' . http_build_query($params);

        $resp = $this->get($url);

        if ($resp->status === false) {
            return $resp;
        } else {
            if (isset($resp->data->ResultSet->Row->ID)) {
                return (object) ['status' => true, 'data' => $resp->data->ResultSet->Row];
            }

            if (isset($resp->data->ResultSet->Row->ERRORCODE)) {
                return (object) [
                    'status' => false,
                    'error' => $resp->data->ResultSet->Row->ERRORCODE,
                    'message' => $resp->data->ResultSet->Row->ERRORMSG
                ];
            }
        }

        return (object) [
            'status' => false,
            'error' => 'Generic Error',
            'message' => 'Unexpected Response: ' . json_encode($resp->data)
        ];
    }

    public function scheduleSearchTypeDailyReport($params)
    {
        if (isset($params['mrkt_id'])) {
            $market = self::market2countries($params['mrkt_id']);
            if ($market === false) {
                return (object) [
                    'status' => false,
                    'error' => 'Unknown market code',
                    'message' => 'Unknown market code "' . $params['mrkt_id'] . '"'
                ];
            }
            $params['mrkt_id'] = $market;
        }

        $av =  $this->getDataAvailability('Daily', 'Search Type', $params['date_begin'], $params['date_end']);

        if ($av->status == false) {
            return $av;
        }

        $dateBegin = str_replace('-', '', $params['date_begin']);
        $dateEnd   = str_replace('-', '', $params['date_end']);
        $default = array(
            'userId'            => 'marcellov_bidberrymedia_pi',
            'attributeList'     => 'product,market,device_type,source tag,type tag,ad_type',
            'channel'           => '',
            'currency'          => 0,
            'dateRange'         => 'CUSTOM',
            'dateRollup'        => 'Daily',
            'device'            => 'ALL',
            'endDate'           => $dateEnd,
            'format'            => 'json',
            'mrkt_id'           => 'ALL',
            'orderBy'           => 'DATA+DATE',
            'partnerList'       => 'ALL',
            'product'           => 'ALL',
            'returnRows'        => 300000,
            'scheEndDate'       => substr($dateEnd, 0, 8),
            'scheStartDate'     => substr($dateBegin, 0, 8),
            'scheduleFrequency' => 1,
            'sourceTag'         => '*',
            'startDate'         => $dateBegin,
            'startRow'          => 1,
            'type'              => 'ASYNC',
        );

        $params['label'] = str_replace(
            ' ',
            '_',
            strtolower(implode('__', [
                'Search Type',
                'Daily',
                $params['mrkt_id'],
                $params['date_begin'],
                $params['date_end']
            ]))
        );
        $params = array_merge($default, $params);

        unset($params['date_begin']);
        unset($params['date_end']);

        $url = $this->api_url . 'getTypeDetailReport?' . http_build_query($params);

        $resp = $this->get($url);

        if ($resp->status === false) {
            return $resp;
        } else {
            if (isset($resp->data->ResultSet->Row->ID)) {
                return (object) ['status' => true, 'data' => $resp->data->ResultSet->Row];
            }

            if (isset($resp->data->ResultSet->Row->ERRORCODE)) {
                return (object) [
                    'status' => false,
                    'error' => $resp->data->ResultSet->Row->ERRORCODE,
                    'message' => $resp->data->ResultSet->Row->ERRORMSG
                ];
            }
        }

        return (object) [
            'status' => false,
            'error' => 'Generic Error',
            'message' => 'Unexpected Response: ' . json_encode($resp->data)
        ];
    }

    public function downloadReport($jobId)
    {

        $params = array(
            'asyncJobId'    => $jobId,
            'format'        => 'json',
            'fileFormat'    => 'json'
        );
        $url = $this->api_url . 'getAsyncJobStatus?' . http_build_query($params);
        $resp = $this->get($url);
        if ($resp->status === false) {
            return false;
        }

        if (isset($resp->ResultSet->Row->ERROR_CODE)) {
            if (isset($resp->ResultSet->Row->ERRORMSG)) {
                return (object) [
                    'status' => false,
                    'error' => $resp->data->ResultSet->Row->ERROR_CODE,
                    'message' => $resp->data->ResultSet->Row->ERRORMSG
                ];
            } elseif (isset($resp->ResultSet->Row->ERROR_MESSAGE)) {
                return (object) [
                    'status' => false,
                    'error' => $resp->data->ResultSet->Row->ERROR_CODE,
                    'message' => $resp->data->ResultSet->Row->ERROR_MESSAGE
                ];
            } else {
                return (object) [
                    'status' => false,
                    'error' => $resp->data->ResultSet->Row->ERROR_CODE,
                    'message' => 'Generic Error'
                ];
            }
        }

        if (isset($resp->data->ResultSet->Row->REPORT_STATUS)) {
            return (object) [
                'status' => true,
                'data' => (object)[
                    'report_status' => $resp->data->ResultSet->Row->REPORT_STATUS,
                    'report_output_file' => $resp->data->ResultSet->Row->REPORT_OUTPUT_FILE,
                    'job_id' => $jobId
                ]
            ];
        } else {
            if (isset($resp->MetaInfo->ResponseStatus) && $resp->MetaInfo->ResponseStatus == 'SUCCESS') {
                return (object) [
                    'status' => false,
                    'data' => (object)[
                        'report_status' => 'Unknown',
                        'report_output_file' => ''
                    ]
                ];
            }
        }

        return (object) [
            'status' => false,
            'error' => $resp->MetaInfo->ResponseStatus ?? 'Unknown',
            'message' => $resp->MetaInfo->ResponseMessage ?? 'Generic Error'
        ];
    }

    public function downloadFile($jobId, $remoteFileUrl, $localFilePath)
    {

        if (empty($this->access_token)) {
            if (!$this->getAccessToken()) {
                return (object) [
                    'status' => false,
                    'error' => 'Empty Token',
                    'message' => 'Unable to Retrieve Token'
                ];
            }
        }
        $client = new Client([
            'verify' => false,
            'headers' => ["Accept-Encoding" => "gzip, deflate, br", 'Authorization' => "Bearer {$this->access_token->access_token}"]
        ]);
        try {
            
            $response = $client->request(
                'GET',
                $remoteFileUrl
            );
            $contentType = $response->getHeaderLine('Content-Type');
            if (strtolower($contentType) != 'text/plain; charset=utf-8' && strtolower($contentType) != 'application/x-zip') {
                Log::warning(json_encode([
                    'log' => '[ARC][YahooLibrary][downloadFile]',
                    'log_type' => 'warning',
                    'status' => false,
                    'exception' => 'Wrong content type',
                    'message' => 'Wrong content type [' . $contentType . ']'
                ]));
                return (object) [
                    'status' => false,
                    'error' => 'Wrong content type',
                    'message' => 'Wrong content type [' . $contentType . ']'
                ];
            }

            if (strtolower($contentType) == 'application/x-zip'){
                
                $fName = $this->tmp_path . '/yahoo_tmp_' . $jobId;
                file_put_contents($fName .'.zip', $response->getBody());
    

                $dt = shell_exec('cd '.$this->tmp_path.'; unzip -o ' .$fName.'.zip');
                $lines = explode("\n", $dt);

                
                
                $file_name = trim(str_replace('inflating:', '', trim($lines[1])));
    
                $resp = file_get_contents($this->tmp_path . $file_name);
                unlink($fName.'.zip');

                $fileName = $this->tmp_path . $file_name;
                
            } else {
                $fileName = $this->tmp_path . '/yahoo_tmp_' . $jobId .'json';
            } 

            if(file_exists($localFilePath)) {
                unlink($localFilePath);
            }
            $x = Storage::disk('system')->move($fileName, $localFilePath);

            if($x) {
                return (object) [
                    'status' => true,
                    'local_file_path' => $localFilePath,
                    'job_id' => $jobId
                ];
            }
            else {
                return (object) [
                    'status' => false,
                    'error' => 'Storage Move Fail',
                    'message' => 'Impossible to move file to localFilePath',
                    'local_file_path' => $localFilePath,
                    'job_id' => $jobId,
                    'temp_file_path' => $fileName,
                ];
            }


        } catch (\GuzzleHttp\Exception\ClientException $e) {
            Log::warning(json_encode([
                'log' => '[ARC][YahooLibrary][downloadFile]',
                'log_type' => 'warning',
                'status' => false,
                'exception' => '\GuzzleHttp\Exception\ClientException',
                'message' => $e->getResponse()->getReasonPhrase()
            ]));
            return (object) [
                'status' => false,
                'error' => '\GuzzleHttp\Exception\ClientException',
                'message' => $e->getResponse()->getReasonPhrase()
            ];
        } catch (\Exception $e) {
            Log::warning(json_encode([
                'log' => '[ARC][YahooLibrary][downloadFile]',
                'log_type' => 'warning',
                'status' => false,
                'exception' => get_class($e),
                'message' => $e->getMessage()
            ]));
            return (object) [
                'status' => false,
                'error' => get_class($e),
                'message' => $e->getMessage()
            ];
        }
    }
}
