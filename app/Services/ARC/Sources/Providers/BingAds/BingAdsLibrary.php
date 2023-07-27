<?php

namespace App\Services\ARC\Sources\Providers\BingAds;

ini_set("soap.wsdl_cache_enabled", 0);
ini_set("default_socket_timeout", 15);

use Illuminate\Support\Facades\Log;
use App\Models\BingAdsAccessToken;
use Illuminate\Support\Str;
use Storage;
use ZipArchive;
use GuzzleHttp\Client;

// Specify the Microsoft\BingAds\Auth classes that will be used.
// use Microsoft\BingAds\Auth\PasswordAuthentication;
// use Microsoft\BingAds\Auth\OAuthDesktopMobileAuthCodeGrant;
use Microsoft\BingAds\Auth\OAuthWebAuthCodeGrant;
use Microsoft\BingAds\Auth\AuthorizationData;
// use Microsoft\BingAds\Auth\OAuthTokenRequestException;
use Microsoft\BingAds\Auth\ApiEnvironment;
use Microsoft\BingAds\Auth\OAuthTokens;

use SoapFault;

// CUSTOMERMANAGEMENTCLASSES CLASS

// Specify the Microsoft\BingAds\V13\CustomerManagement classes that will be used.
// use Microsoft\BingAds\V13\CustomerManagement\GetCustomerPilotFeaturesRequest;
// use Microsoft\BingAds\V13\CustomerManagement\GetUserRequest;
// use Microsoft\BingAds\V13\CustomerManagement\AddClientLinksRequest;
// use Microsoft\BingAds\V13\CustomerManagement\SearchClientLinksRequest;
// use Microsoft\BingAds\V13\CustomerManagement\SignupCustomerRequest;
// use Microsoft\BingAds\V13\CustomerManagement\UpdateClientLinksRequest;
// use Microsoft\BingAds\V13\CustomerManagement\SearasdchAccountsRequest;
// use Microsoft\BingAds\V13\CustomerManagement\Paging;
// use Microsoft\BingAds\V13\CustomerManagement\Predicate;
// use Microsoft\BingAds\V13\CustomerManagement\PredicateOperator;
use Microsoft\BingAds\V13\CustomerManagement\GetAccountsInfoRequest;
use Microsoft\BingAds\V13\CustomerManagement\GetCustomersInfoRequest;


// Specify the Microsoft\BingAds\V13\Reporting classes that will be used.
use Microsoft\BingAds\V13\Reporting\SubmitGenerateReportRequest;
use Microsoft\BingAds\V13\Reporting\PollGenerateReportRequest;
// use Microsoft\BingAds\V13\Reporting\AccountPerformanceReportRequest;
use Microsoft\BingAds\V13\Reporting\CampaignPerformanceReportRequest;
use Microsoft\BingAds\V13\Reporting\KeywordPerformanceReportRequest;
use Microsoft\BingAds\V13\Reporting\ReportFormat;
use Microsoft\BingAds\V13\Reporting\ReportAggregation;
use Microsoft\BingAds\V13\Reporting\AccountThroughAdGroupReportScope;
// use Microsoft\BingAds\V13\Reporting\CampaignReportScope;
// use Microsoft\BingAds\V13\Reporting\AccountReportScope;
use Microsoft\BingAds\V13\Reporting\ReportTime;
// use Microsoft\BingAds\V13\Reporting\ReportTimePeriod;
use Microsoft\BingAds\V13\Reporting\Date;
use Microsoft\BingAds\V13\CampaignManagement\GetCampaignsByAccountIdRequest;
use Microsoft\BingAds\V13\Reporting\CampaignPerformanceReportFilter;
use Microsoft\BingAds\V13\Reporting\KeywordPerformanceReportFilter;
//use Microsoft\BingAds\V13\Reporting\DeviceTypeReportFilter;
use Microsoft\BingAds\V13\Reporting\CampaignPerformanceReportColumn;
// use Microsoft\BingAds\V13\Reporting\AudiencePerformanceReportColumn;
use Microsoft\BingAds\V13\Reporting\KeywordPerformanceReportColumn;
use Microsoft\BingAds\V13\Reporting\ReportRequestStatusType;
use Microsoft\BingAds\V13\Reporting\KeywordPerformanceReportSort;
use Microsoft\BingAds\V13\Reporting\CampaignPerformanceReportSort;
use Microsoft\BingAds\V13\Reporting\SortOrder;

// use Microsoft\BingAds\V13\Reporting\AdGroupPerformanceReportRequest;
// use Microsoft\BingAds\V13\Reporting\AdGroupPerformanceReportFilter;
// use Microsoft\BingAds\V13\Reporting\AdGroupPerformanceReportColumn;

// Specify the Microsoft\BingAds\Auth classes that will be used.
use Microsoft\BingAds\Auth\ServiceClient;
use Microsoft\BingAds\Auth\ServiceClientType;

use Exception;
use GuzzleHttp\Exception\ClientException;


class BingAdsLibrary
{

    protected $_client = null;

    /*-- BingAds API load config --*/
    protected $parent_customer_id;
    protected $client_id;

    protected $client_secret;

    protected $url;

    protected $developer_token;

    protected $tmp_path;

    /** @var null $_reportingProxy */
    protected $_reportingProxy = null;

    /** @var null $_customerProxy */
    protected $_customerProxy  = null;

    

    protected $_campaignManagementProxy;
    protected $authorizationData;
    protected $authentication;

    protected $error;

    public function __construct()
    {
        /*-- Load config --*/
        $this->parent_customer_id        = config('arc.sources.bingads.parent_customer_id');
        $this->client_id        = config('arc.sources.bingads.client_id');
        $this->client_secret    = config('arc.sources.bingads.client_secret');
        $this->url              = config('arc.sources.bingads.url');
        $this->redirect_uri     = config('arc.sources.bingads.redirect_uri');
        $this->developer_token  = config('arc.sources.bingads.developer_token');
        $this->tmp_path         = config('arc.tmp_path');

        $this->client = new Client(['verify' => false]);
    }

    public function getError()
    {
        return $this->error;
    }

    /**
     * @param $encodedReport
     * @return bool
     */
    private function downloadReport($encodedReport, $reportFile)
    {
        /*--
            SubmitGenerateReport helper method calls the corresponding Bing Ads service operation
            to request the report identifier. The identifier is used to check report generation status
            before downloading the report
        --*/
        $reportRequestId = $this->submitGenerateReport($encodedReport);

        if (empty($reportRequestId)) {
            Log::warning('[BingAdsLibrary] Request not generated');
            $this->error = (object) ['error' => 'Request not generated'];
            return false;
        }

        $reportRequestStatus = null;
        $waitTime = 5;

        /*--
            This sample polls every 30 seconds up to 5 minutes.
            In production you may poll the status every 1 to 2 minutes for up to one hour.
            If the call succeeds, stop polling. If the call or
            download fails, the call throws a fault
        --*/
        Log::info('[BingAdsLibrary] - Getting report to download');
        for ($i = 0; $i < 10; $i++) {
            sleep($waitTime);
            /*--
                PollGenerateReport helper method calls the corresponding Bing Ads service operation
                to get the report request status
            --*/
            $reportRequestStatus = $this->pollGenerateReport($reportRequestId)->ReportRequestStatus;

            if (
                $reportRequestStatus->Status == ReportRequestStatusType::Success ||
                $reportRequestStatus->Status == ReportRequestStatusType::Error
            ) {
                break;
            }
        }

        /*-- Check if report request status is different than null --*/
        if ($reportRequestStatus != null) {
            if ($reportRequestStatus->Status == ReportRequestStatusType::Success) {
                $reportDownloadUrl = $reportRequestStatus->ReportDownloadUrl;

                /*-- Report download URL is empty. Stop script. --*/
                if (empty($reportDownloadUrl)) {
                    Log::info('[BingAdsLibrary] - Report is empty, creating and empty file');
                    Storage::disk('system')->put($reportFile, '');

                    return true;
                }

                /*-- Get Report with the extension ZIP by requesting consumer and saving it under tmp_path --*/
                try {
                    $bingAdsData = $this->client->get($reportDownloadUrl, ['debug' => false]);
                    $tmp_file = realpath($this->tmp_path) .'/'. $reportRequestId . '.zip';
                    
                    Storage::disk('system')->put($tmp_file, $bingAdsData->getBody());

                    Log::info('[BingAdsLibrary] - Report has been downloaded with success');

                    // Uncompress and read the ZIP archive
                    $zip = new ZipArchive;
                    if (true === $zip->open($tmp_file)) {
                        Log::info('[BingAdsLibrary] - ' . " Extracting {$tmp_file} to {$this->tmp_path}");
                        $filename = $zip->getNameIndex(0);
                        $tmp_csv_file = realpath($this->tmp_path) . '/'. $filename;
                        $zip->extractTo($this->tmp_path);
                        $zip->close();
                        if (file_exists($tmp_csv_file)) {
                            Log::info('[BingAdsLibrary] - ' . " Renaming {$tmp_csv_file} to {$reportFile}");

                            rename($tmp_csv_file, $reportFile);
                            unlink($tmp_file);
                            return true;
                        } else {
                            Log::error('[BingAdsLibrary] - Unable to find unzipped report file: ' . $tmp_csv_file);
                            $this->error = (object) ['error' => 'Unable to find unzipped report file: ' . $tmp_csv_file];
                            return false;
                        }
                    } else {
                        Log::error('[BingAdsLibrary] - Unable to unzip the report file');
                        $this->error = (object) ['error' => 'Unable to unzip the report file'];

                        return false;
                    }
                } catch (Exception $e) {
                    if ($e->getPrevious()) {; // Ignore fault exceptions that we already caught.
                    } else {
                        Log::error('[BingAdsLibrary] - ' . $e->getCode() . ' - ' . $e->getMessage() . ' - ' . $e->getTraceAsString());
                        $this->error = (object) ['error' => $e->getMessage()];

                        return false;
                    }
                    // Log::error('[BAI-BGC-005] - ...');
                }
            } else if ($reportRequestStatus->Status == ReportRequestStatusType::Error) {
                Log::warning('[BingAdsLibrary] - The request failed. Try requesting the report later. If the request continues to fail, contact support.');
                $this->error = (object) ['error' => 'The request failed. Try requesting the report later. If the request continues to fail, contact support.'];
            } else {
                Log::warning('[BingAdsLibrary] - The request is taking longer than expected. Save the report ID (%s) and try again later.' . $reportRequestId);
                $this->error = (object) ['error' => 'The request is taking longer than expected. Save the report ID (%s) and try again later.' . $reportRequestId];
            }
            return false;
        }
        Log::error('[BingAdsLibrary] - No Response from service');
        $this->error = (object) ['error' => ' No Response from service'];
        return false;
    }

    /**
     * @param bool $onlyActive
     * @return array
     */
    public function getAllAccounts($onlyActive = false)
    {
        try {
            Log::info('[BingAdsLibrary] - Getting customers');
            $cInfos = $this->getCustomers();
            $accounts = array();
            if (is_object($cInfos) && isset($cInfos->CustomerInfo)) {
                foreach ($cInfos->CustomerInfo as $cInfo) {
                    if (!is_null($cInfo)) {
                        $aInfos = $this->getAccounts($cInfo->Id, $this->_customerProxy);
                        if (is_object($aInfos)) {
                            foreach ($aInfos->AccountInfo as $aInfo) {
                                if (!is_null($aInfo)) {
                                    $account = new \stdClass;
                                    $account->id         = $aInfo->Id;
                                    $account->number     = $aInfo->Number;
                                    $account->name       = $aInfo->Name;
                                    $account->status     = $aInfo->AccountLifeCycleStatus;
                                    $account->customerId = $cInfo->Id;

                                    /*-- Verify whether Account is active or not --*/
                                    if ($onlyActive) {
                                        // if ($account->__get('status') == 'Active') {
                                        //     $accounts[] = $account;
                                        // }
                                    } else {
                                        $accounts[] = $account;
                                    }
                                }
                            }
                        } else {
                            Log::warning('[BingAdsLibrary] - These account info are contained into a non-object: ' . json_encode($cInfo));
                        }
                    }
                }
            } else {
                throw new Exception("Empty Customer Info");
            }
            return $accounts;
        } catch (SoapFault $e) {
            /*-- Reporting service operations can throw AdApiFaultDetail --*/
            if (isset($e->detail->AdApiFaultDetail)) {
                /*-- Log this fault --*/
                Log::error('[BingAdsLibrary] - The operation failed with the following faults:');

                $errors = is_array($e->detail->AdApiFaultDetail->Errors->AdApiError)
                    ? $e->detail->AdApiFaultDetail->Errors->AdApiError
                    : array('AdApiError' => $e->detail->AdApiFaultDetail->Errors->AdApiError);

                /*-- If the AdApiError array is not null, the following are examples of error codes that may be found --*/
                foreach ($errors as $error) {
                    Log::error('[BingAdsLibrary][ERROR]: ' . json_encode($error));
                    switch ($error->Code) {
                        case 0:    // InternalError
                            break;
                        case 105:  // InvalidCredentials
                            break;
                        default:
                            Log::error('[BingAdsLibrary] - Please see MSDN documentation for more details about the error code output above.');
                            break;
                    }
                }
                return false;
            } elseif (isset($e->detail->ApiFaultDetail)) {
                /*-- Reporting service operations can throw ApiFaultDetail, log this fault --*/
                Log::error('[BingAdsLibrary] - The operation failed with the following faults:');
                /*-- If the BatchError array is not null, the following are examples of error codes that may be found --*/
                if (!empty($e->detail->ApiFaultDetail->BatchErrors)) {
                    $errors = is_array($e->detail->ApiFaultDetail->BatchErrors->BatchError)
                        ? $e->detail->ApiFaultDetail->BatchErrors->BatchError
                        : array('BatchError' => $e->detail->ApiFaultDetail->BatchErrors->BatchError);
                    foreach ($errors as $error) {
                        Log::error('[BingAdsLibrary][ERROR][BATCH]: ' . json_encode($error));

                        switch ($error->Code) {
                            case 0:     // InternalError
                                break;
                            default:
                                Log::error('[BingAdsLibrary] - Please see MSDN documentation for more details about the error code output above.');
                                break;
                        }
                    }
                    return false;
                }
                /*-- If the OperationError array is not null, the following are examples of error codes that may be found --*/
                if (!empty($e->detail->ApiFaultDetail->OperationErrors)) {
                    $errors = is_array($e->detail->ApiFaultDetail->OperationErrors->OperationError)
                        ? $e->detail->ApiFaultDetail->OperationErrors->OperationError
                        : array('OperationError' => $e->detail->ApiFaultDetail->OperationErrors->OperationError);
                    foreach ($errors as $error) {
                        Log::error('[BingAdsLibrary][ERROR][OPERATION]: ' . json_encode($error));

                        switch ($error->Code) {
                            case 0:     // InternalError
                                break;
                            case 106:   // UserIsNotAuthorized
                                break;
                            case 2100:  // ReportingServiceInvalidReportId
                                break;
                            default:
                                Log::error('[BingAdsLibrary] - Please see MSDN documentation for more details about the error code output above.');
                                break;
                        }
                    }
                    return false;
                }
            }
        } catch (Exception $e) {
            if ($e->getPrevious()) {; // Ignore fault exceptions that we already caught.
            } else {
                Log::error('[BingAdsLibrary] - ' . $e->getCode() . ' - ' . $e->getMessage() . ' - ' . $e->getTraceAsString());
            }
            return false;
        }
        return array();
    }

    /**
     * @param $customerId
     * @return array
     */
    public function getAccounts($customerId)
    {
        $this->_last_error = "";
        try {
            /*--
                Request only parent accounts such that the returned accounts
                are within the current customer identifier, and not managed
                through an agency or reseller
            --*/
            $request = new GetAccountsInfoRequest();
            $request->OnlyParentAccounts = "true";
            $request->CustomerId = $customerId;

            return $this->_customerProxy->GetService()->GetAccountsInfo($request)->AccountsInfo;
        } catch (SoapFault $e) {
            /*-- Output the last request/response --*/
            Log::error('[BingAdsLibrary] - Last SOAP request/response: ' .
                $this->_customerProxy->GetWsdl() . "\n" .
                $this->_customerProxy->GetService()->__getLastRequest() . "\n" .
                $this->_customerProxy->GetService()->__getLastResponse());

            /*-- Reporting service operations can throw AdApiFaultDetail --*/
            if (isset($e->detail->AdApiFaultDetail)) {
                /*-- Log this fault --*/
                Log::error('[BingAdsLibrary] - The operation failed with the following faults:');
                $errors = is_array($e->detail->AdApiFaultDetail->Errors->AdApiError)
                    ? $e->detail->AdApiFaultDetail->Errors->AdApiError
                    : array('AdApiError' => $e->detail->AdApiFaultDetail->Errors->AdApiError);
                /*-- If the AdApiError array is not null, the following are examples of error codes that may be found --*/
                foreach ($errors as $error) {
                    Log::error('[BingAdsLibrary][ERROR][API]: ' . json_encode($error));

                    switch ($error->Code) {
                        case 0:    // InternalError
                            break;
                        case 105:  // InvalidCredentials
                            break;
                        default:
                            Log::error('[BingAdsLibrary] - Please see MSDN documentation for more details about the error code output above.');
                            break;
                    }
                }
            } elseif (isset($e->detail->ApiFaultDetail)) {
                /*-- Reporting service operations can throw ApiFaultDetail, log this fault --*/
                Log::error('[BingAdsLibrary] - The operation failed with the following faults:');
                /*-- If the BatchError array is not null, the following are examples of error codes that may be found --*/
                if (!empty($e->detail->ApiFaultDetail->BatchErrors)) {
                    $errors = is_array($e->detail->ApiFaultDetail->BatchErrors->BatchError)
                        ? $e->detail->ApiFaultDetail->BatchErrors->BatchError
                        : array('BatchError' => $e->detail->ApiFaultDetail->BatchErrors->BatchError);
                    foreach ($errors as $error) {
                        Log::error('[BingAdsLibrary][ERROR][BATCH]: ' . json_encode($error));

                        switch ($error->Code) {
                            case 0:     // InternalError
                                break;
                            default:
                                Log::error('[BingAdsLibrary] - Please see MSDN documentation for more details about the error code output above.');
                                break;
                        }
                    }
                }
                /*-- If the OperationError array is not null, the following are examples of error codes that may be found --*/
                if (!empty($e->detail->ApiFaultDetail->OperationErrors)) {
                    $errors = is_array($e->detail->ApiFaultDetail->OperationErrors->OperationError)
                        ? $e->detail->ApiFaultDetail->OperationErrors->OperationError
                        : array('OperationError' => $e->detail->ApiFaultDetail->OperationErrors->OperationError);
                    foreach ($errors as $error) {
                        Log::error('[BingAdsLibrary][ERROR][Operation]: ' . json_encode($error));

                        switch ($error->Code) {
                            case 0:     // InternalError
                                break;
                            case 106:   // UserIsNotAuthorized
                                break;
                            case 2100:  // ReportingServiceInvalidReportId
                                break;
                            default:
                                Log::error('[BingAdsLibrary] - Please see MSDN documentation for more details about the error code output above.');
                                break;
                        }
                    }
                }
            }
        } catch (Exception $e) {
            if ($e->getPrevious()) { // Ignore fault exceptions that we already caught.
            } else {
                Log::error('[BingAdsLibrary] - ' . $e->getCode() . ' - ' . $e->getMessage() . ' - ' . $e->getTraceAsString());
            }
        }
        return array();
    }


    public function getCampaigns($account_id)
    {
        try {
            $this->error = [];
            $authorizationData = (new AuthorizationData())
             ->withAuthentication($this->authentication)
             ->withCustomerId($this->parent_customer_id)
             ->withAccountId($account_id)
             ->withDeveloperToken($this->developer_token);
            
            $this->_campaignManagementProxy->SetAuthorizationData($authorizationData);
            
            $request = new GetCampaignsByAccountIdRequest();
            $request->AccountId = $account_id;
            $request->CampaignType = 'Audience Search Shopping DynamicSearchAds';
            return $this->_campaignManagementProxy->GetService()->GetCampaignsByAccountId($request);
        } catch (SoapFault $e) {
            /*-- Output the last request/response --*/
            Log::error('[BingAdsLibrary] - Last SOAP request/response: ' .
                $this->_campaignManagementProxy->GetWsdl() . "\n" .
                $this->_campaignManagementProxy->GetService()->__getLastRequest() . "\n" .
                $this->_campaignManagementProxy->GetService()->__getLastResponse());
            $this->error[] =$this->_campaignManagementProxy->GetService()->__getLastResponse(); 
            /*-- Reporting service operations can throw AdApiFaultDetail --*/
            if (isset($e->detail->AdApiFaultDetail)) {
                /*-- Log this fault --*/
                Log::error('[BingAdsLibrary] - The operation failed with the following faults:');
                $errors = is_array($e->detail->AdApiFaultDetail->Errors->AdApiError)
                    ? $e->detail->AdApiFaultDetail->Errors->AdApiError
                    : array('AdApiError' => $e->detail->AdApiFaultDetail->Errors->AdApiError);
                /*-- If the AdApiError array is not null, the following are examples of error codes that may be found --*/
                foreach ($errors as $error) {
                    Log::error('[BingAdsLibrary][ERROR][API]: ' . json_encode($error));
                    $this->error[] = $error;
                    switch ($error->Code) {
                        case 0:    // InternalError
                            break;
                        case 105:  // InvalidCredentials
                            break;
                        default:
                            Log::error('[BingAdsLibrary] - Please see MSDN documentation for more details about the error code output above.');
                            break;
                    }
                }
            } elseif (isset($e->detail->ApiFaultDetail)) {
                /*-- Reporting service operations can throw ApiFaultDetail, log this fault --*/
                Log::error('[BingAdsLibrary] - The operation failed with the following faults:');
                /*-- If the BatchError array is not null, the following are examples of error codes that may be found --*/
                if (!empty($e->detail->ApiFaultDetail->BatchErrors)) {
                    $errors = is_array($e->detail->ApiFaultDetail->BatchErrors->BatchError)
                        ? $e->detail->ApiFaultDetail->BatchErrors->BatchError
                        : array('BatchError' => $e->detail->ApiFaultDetail->BatchErrors->BatchError);
                    foreach ($errors as $error) {
                        Log::error('[BingAdsLibrary][ERROR][BATCH]: ' . json_encode($error));
                        $this->error[] = $error;
                        switch ($error->Code) {
                            case 0:     // InternalError
                                break;
                            default:
                                Log::error('[BingAdsLibrary] - Please see MSDN documentation for more details about the error code output above.');
                                break;
                        }
                    }
                }
                /*-- If the OperationError array is not null, the following are examples of error codes that may be found --*/
                if (!empty($e->detail->ApiFaultDetail->OperationErrors)) {
                    $errors = is_array($e->detail->ApiFaultDetail->OperationErrors->OperationError)
                        ? $e->detail->ApiFaultDetail->OperationErrors->OperationError
                        : array('OperationError' => $e->detail->ApiFaultDetail->OperationErrors->OperationError);
                    foreach ($errors as $error) {
                        Log::error('[BingAdsLibrary][ERROR][Operation]:: ' . json_encode($error));
                        switch ($error->Code) {
                            case 0:     // InternalError
                                break;
                            case 106:   // UserIsNotAuthorized
                                break;
                            case 2100:  // ReportingServiceInvalidReportId
                                break;
                            default:
                                Log::error('[BingAdsLibrary] - Please see MSDN documentation for more details about the error code output above.');
                                break;
                        }
                    }
                }
            }
        } catch (Exception $e) {
            if ($e->getPrevious()) {; // Ignore fault exceptions that we already caught.
            } else {
                Log::error('[BingAdsLibrary] - ' . $e->getCode() . ' - ' . $e->getMessage() . ' - ' . $e->getTraceAsString());
            }
        }
        return array();
    }

    /**
     * @return array
     */
    public function getCustomers()
    {

        return (object) [
            'CustomerInfo' => [
                (object) [
                    'Id' => '250481630',
                    'Name' => 'BidBerry srl'
                ]
            ]
                ];
        try {
            $request = new GetCustomersInfoRequest();
            $request->CustomerNameFilter = '';
            $request->TopN = 100;

            return $this->_customerProxy->GetService()->GetCustomersInfo($request)->CustomersInfo;
        } catch (SoapFault $e) {
            /*-- Output the last request/response --*/
            Log::error('[BingAdsLibrary] - Last SOAP request/response: ' .
                $this->_customerProxy->GetWsdl() . "\n" .
                $this->_customerProxy->GetService()->__getLastRequest() . "\n" .
                $this->_customerProxy->GetService()->__getLastResponse());

                
            /*-- Reporting service operations can throw AdApiFaultDetail --*/
            if (isset($e->detail->AdApiFaultDetail)) {
                /*-- Log this fault --*/
                Log::error('[BingAdsLibrary] - The operation failed with the following faults:');
                $errors = is_array($e->detail->AdApiFaultDetail->Errors->AdApiError)
                    ? $e->detail->AdApiFaultDetail->Errors->AdApiError
                    : array('AdApiError' => $e->detail->AdApiFaultDetail->Errors->AdApiError);
                /*-- If the AdApiError array is not null, the following are examples of error codes that may be found --*/
                foreach ($errors as $error) {
                    Log::error('[BingAdsLibrary][ERROR][API]: ' . json_encode($error));

                    switch ($error->Code) {
                        case 0:    // InternalError
                            break;
                        case 105:  // InvalidCredentials
                            break;
                        default:
                            Log::error('[BingAdsLibrary] - Please see MSDN documentation for more details about the error code output above.');
                            break;
                    }
                }
            } elseif (isset($e->detail->ApiFaultDetail)) {
                /*-- Reporting service operations can throw ApiFaultDetail, log this fault --*/
                Log::error('[BingAdsLibrary] - The operation failed with the following faults:');
                /*-- If the BatchError array is not null, the following are examples of error codes that may be found --*/
                if (!empty($e->detail->ApiFaultDetail->BatchErrors)) {
                    $errors = is_array($e->detail->ApiFaultDetail->BatchErrors->BatchError)
                        ? $e->detail->ApiFaultDetail->BatchErrors->BatchError
                        : array('BatchError' => $e->detail->ApiFaultDetail->BatchErrors->BatchError);
                    foreach ($errors as $error) {
                        Log::error('[BingAdsLibrary][ERROR][BATCH]: ' . json_encode($error));

                        switch ($error->Code) {
                            case 0:     // InternalError
                                break;
                            default:
                                Log::error('[BingAdsLibrary] - Please see MSDN documentation for more details about the error code output above.');
                                break;
                        }
                    }
                }
                /*-- If the OperationError array is not null, the following are examples of error codes that may be found --*/
                if (!empty($e->detail->ApiFaultDetail->OperationErrors)) {
                    $errors = is_array($e->detail->ApiFaultDetail->OperationErrors->OperationError)
                        ? $e->detail->ApiFaultDetail->OperationErrors->OperationError
                        : array('OperationError' => $e->detail->ApiFaultDetail->OperationErrors->OperationError);
                    foreach ($errors as $error) {
                        Log::error('[BingAdsLibrary][ERROR][Operation]:: ' . json_encode($error));
                        switch ($error->Code) {
                            case 0:     // InternalError
                                break;
                            case 106:   // UserIsNotAuthorized
                                break;
                            case 2100:  // ReportingServiceInvalidReportId
                                break;
                            default:
                                Log::error('[BingAdsLibrary] - Please see MSDN documentation for more details about the error code output above.');
                                break;
                        }
                    }
                }
            } elseif(isset($e->faultstring)) {
                Log::error('[BingAdsLibrary] - ' . $e->getMessage() . "({$e->getFile()} #{$e->getLine()})");
            }
        } catch (Exception $e) {
            if ($e->getPrevious()) {; // Ignore fault exceptions that we already caught.
            } else {
                Log::error('[BingAdsLibrary] - ' . $e->getCode() . ' - ' . $e->getMessage() . ' - ' . $e->getTraceAsString());
            }
        }
        return array();
    }

    /**
     * @param array $accounts_id
     * @param $date
     * @return bool
     */
    public function getKeywordPerformanceReport($accounts_id = array(), $date = '', $reportFile ='')
    {
        try {
            /*-- You can submit one of the example reports, or build your own. --*/
            $report = new KeywordPerformanceReportRequest();

            $report->Format = ReportFormat::Csv;
            $report->ReportName = 'My Keyword Performance Report';
            $report->ReturnOnlyCompleteData = true;
            $report->Aggregation = ReportAggregation::Daily;

            $report->Scope = new AccountThroughAdGroupReportScope();

            $report->Scope->AccountIds = $accounts_id;
            $report->Scope->AdGroups = null;
            $report->Scope->Campaigns = array();

            $report->Time = new ReportTime();

            //  You may either use a custom date range or predefined time.
            $report->Time->CustomDateRangeStart = new Date();
            $report->Time->CustomDateRangeStart->Day = date('d', strtotime($date));
            $report->Time->CustomDateRangeStart->Month = date('m', strtotime($date));
            $report->Time->CustomDateRangeStart->Year = date('Y', strtotime($date));
            $report->Time->CustomDateRangeEnd = new Date();
            $report->Time->CustomDateRangeEnd->Day = date('d', strtotime($date));
            $report->Time->CustomDateRangeEnd->Month = date('m', strtotime($date));
            $report->Time->CustomDateRangeEnd->Year = date('Y', strtotime($date));

            $report->Filter = new KeywordPerformanceReportFilter();

            $report->Columns = array(
                KeywordPerformanceReportColumn::TimePeriod,
                KeywordPerformanceReportColumn::AccountName,
                KeywordPerformanceReportColumn::AccountId,
                KeywordPerformanceReportColumn::AccountNumber,
                KeywordPerformanceReportColumn::CampaignName,
                KeywordPerformanceReportColumn::AdGroupName,
                KeywordPerformanceReportColumn::Keyword,
                KeywordPerformanceReportColumn::AdDistribution,
                KeywordPerformanceReportColumn::DeviceType,
                KeywordPerformanceReportColumn::BidMatchType,
                KeywordPerformanceReportColumn::DeliveredMatchType,
                KeywordPerformanceReportColumn::BidStrategyType,
                KeywordPerformanceReportColumn::KeywordStatus,
                KeywordPerformanceReportColumn::CampaignStatus,
                KeywordPerformanceReportColumn::Impressions,
                KeywordPerformanceReportColumn::Clicks,
                KeywordPerformanceReportColumn::AverageCpc,
                KeywordPerformanceReportColumn::Spend,
                KeywordPerformanceReportColumn::CurrencyCode,
                KeywordPerformanceReportColumn::FinalUrl,

                KeywordPerformanceReportColumn::Conversions,
                KeywordPerformanceReportColumn::CampaignId,
                KeywordPerformanceReportColumn::AdGroupId,
                KeywordPerformanceReportColumn::KeywordId,
                KeywordPerformanceReportColumn::CurrentMaxCpc,
                KeywordPerformanceReportColumn::QualityScore,
                KeywordPerformanceReportColumn::Ctr,
                KeywordPerformanceReportColumn::AveragePosition,
                KeywordPerformanceReportColumn::KeywordLabels
            );

            /*--
                You may optionally sort by any CampaignPerformanceReportColumn, and optionally
                specify the maximum number of rows to return in the sorted report
            --*/

            $report->Sort = array();
            $keywordPerformanceReportSort = new KeywordPerformanceReportSort();
            $keywordPerformanceReportSort->SortColumn = KeywordPerformanceReportColumn::TimePeriod;
            $keywordPerformanceReportSort->SortColumn = KeywordPerformanceReportColumn::AccountName;
            $keywordPerformanceReportSort->SortColumn = KeywordPerformanceReportColumn::AccountId;
            $keywordPerformanceReportSort->SortColumn = KeywordPerformanceReportColumn::AccountNumber;
            $keywordPerformanceReportSort->SortColumn = KeywordPerformanceReportColumn::CampaignName;

            $keywordPerformanceReportSort->SortOrder = SortOrder::Ascending;
            $report->Sort[] = $keywordPerformanceReportSort;

            $encodedReport = new \SoapVar($report, SOAP_ENC_OBJECT, 'KeywordPerformanceReportRequest', $this->_reportingProxy->GetNamespace());

            return $this->downloadReport($encodedReport, $reportFile);
        } catch (SoapFault $e) {
            /*-- Output the last request/response --*/
            Log::error('[BingAdsLibrary] - Last SOAP request/response: ' .
                $this->_reportingProxy->GetWsdl() . "\n" .
                $this->_reportingProxy->GetService()->__getLastRequest() . "\n" .
                $this->_reportingProxy->GetService()->__getLastResponse());

            /*-- Reporting service operations can throw AdApiFaultDetail --*/
            if (isset($e->detail->AdApiFaultDetail)) {
                /*-- Log this fault --*/
                Log::error('[BingAdsLibrary] - The operation failed with the following faults: ');
                $errors = is_array($e->detail->AdApiFaultDetail->Errors->AdApiError)
                    ? $e->detail->AdApiFaultDetail->Errors->AdApiError
                    : array('AdApiError' => $e->detail->AdApiFaultDetail->Errors->AdApiError);
                /*-- If the AdApiError array is not null, the following are examples of error codes that may be found --*/
                foreach ($errors as $error) {
                    Log::error('[BingAdsLibrary][ERROR]: ' . json_encode($error));

                    switch ($error->Code) {
                        case 0:    // InternalError
                            break;
                        case 105:  // InvalidCredentials
                            break;
                        default:
                            Log::error('[BingAdsLibrary] - Please see MSDN documentation for more details about the error code output above.');
                            break;
                    }
                }
                throw new Exception($e);
            } elseif (isset($e->detail->ApiFaultDetail)) {
                /*-- Reporting service operations can throw ApiFaultDetail, log this fault --*/
                Log::error('[BingAdsLibrary] - The operation failed with the following faults: ');
                /*-- If the BatchError array is not null, the following are examples of error codes that may be found --*/
                if (!empty($e->detail->ApiFaultDetail->BatchErrors)) {
                    $errors = is_array($e->detail->ApiFaultDetail->BatchErrors->BatchError)
                        ? $e->detail->ApiFaultDetail->BatchErrors->BatchError
                        : array('BatchError' => $e->detail->ApiFaultDetail->BatchErrors->BatchError);
                    foreach ($errors as $error) {
                        Log::error('[BingAdsLibrary][ERROR][BATCH]: ' . json_error($error));

                        switch ($error->Code) {
                            case 0:     // InternalError
                                break;
                            default:
                                Log::error('[BingAdsLibrary] - Please see MSDN documentation for more details about the error code output above.');
                                break;
                        }
                    }
                }
                /*-- If the OperationError array is not null, the following are examples of error codes that may be found --*/
                if (!empty($e->detail->ApiFaultDetail->OperationErrors)) {
                    $errors = is_array($e->detail->ApiFaultDetail->OperationErrors->OperationError)
                        ? $e->detail->ApiFaultDetail->OperationErrors->OperationError
                        : array('OperationError' => $e->detail->ApiFaultDetail->OperationErrors->OperationError);
                    foreach ($errors as $error) {
                        Log::error('[BingAdsLibrary][ERROR][OPERATION]: ' . json_encode($error));

                        switch ($error->Code) {
                            case 0:     // InternalError
                                break;
                            case 106:   // UserIsNotAuthorized
                                break;
                            case 2100:  // ReportingServiceInvalidReportId
                                break;
                            default:
                                Log::error('[BingAdsLibrary] - Please see MSDN documentation for more details about the error code output above.');
                                break;
                        }
                    }
                }
                throw new Exception($e);
            }
        } catch (Exception $e) {
            if ($e->getPrevious()) {; // Ignore fault exceptions that we already caught.
            } else {
                Log::error('[BingAdsLibrary] - ' . $e->getCode() . ' - ' . $e->getMessage() . ' - ' . $e->getTraceAsString());
            }
            throw new Exception($e);
        }
        return false;
    }


    public function getCampaignPerformanceReport($accounts_id = array(), $date = '', $reportFile = '')
    {
        try {
            /*-- You can submit one of the example reports, or build your own. --*/
            $report = new CampaignPerformanceReportRequest();

            $report->Format = ReportFormat::Csv;
            $report->ReportName = 'My Campaing Performance Report';
            $report->ReturnOnlyCompleteData = true;
            $report->Aggregation = ReportAggregation::Daily;

            $report->Scope = new AccountThroughAdGroupReportScope();

            $report->Scope->AccountIds = $accounts_id;
            $report->Scope->AdGroups = null;
            $report->Scope->Campaigns = array();

            $report->Time = new ReportTime();

            //  You may either use a custom date range or predefined time.
            $report->Time->CustomDateRangeStart = new Date();
            $report->Time->CustomDateRangeStart->Day = date('d', strtotime($date));
            $report->Time->CustomDateRangeStart->Month = date('m', strtotime($date));
            $report->Time->CustomDateRangeStart->Year = date('Y', strtotime($date));
            $report->Time->CustomDateRangeEnd = new Date();
            $report->Time->CustomDateRangeEnd->Day = date('d', strtotime($date));
            $report->Time->CustomDateRangeEnd->Month = date('m', strtotime($date));
            $report->Time->CustomDateRangeEnd->Year = date('Y', strtotime($date));

            $report->Filter = new CampaignPerformanceReportFilter();

            $report->Columns = array(
                CampaignPerformanceReportColumn::AccountId,
                CampaignPerformanceReportColumn::AccountName,
                CampaignPerformanceReportColumn::AccountNumber,
                CampaignPerformanceReportColumn::AdDistribution,
                CampaignPerformanceReportColumn::AverageCpc,
                CampaignPerformanceReportColumn::AveragePosition,
                CampaignPerformanceReportColumn::BidMatchType,
                CampaignPerformanceReportColumn::BudgetAssociationStatus,
                CampaignPerformanceReportColumn::BudgetName,
                CampaignPerformanceReportColumn::BudgetStatus,
                CampaignPerformanceReportColumn::CampaignId,
                CampaignPerformanceReportColumn::CampaignLabels,
                CampaignPerformanceReportColumn::CampaignName,
                CampaignPerformanceReportColumn::CampaignStatus,
                CampaignPerformanceReportColumn::CampaignType,
                CampaignPerformanceReportColumn::Clicks,
                CampaignPerformanceReportColumn::Conversions,
                CampaignPerformanceReportColumn::Ctr,
                CampaignPerformanceReportColumn::CurrencyCode,
                CampaignPerformanceReportColumn::DeliveredMatchType,
                CampaignPerformanceReportColumn::DeviceType,
                CampaignPerformanceReportColumn::Impressions,
                CampaignPerformanceReportColumn::Network,
                CampaignPerformanceReportColumn::QualityScore,
                CampaignPerformanceReportColumn::Spend,
                CampaignPerformanceReportColumn::TimePeriod,
            );

            /*--
                You may optionally sort by any CampaignPerformanceReportColumn, and optionally
                specify the maximum number of rows to return in the sorted report
            --*/

            // $report->Sort = array();
            // $keywordPerformanceReportSort = new CampaignPerformanceReportSort();
            // $keywordPerformanceReportSort->SortColumn = CampaignPerformanceReportColumn::TimePeriod;
            // $keywordPerformanceReportSort->SortColumn = CampaignPerformanceReportColumn::AccountName;
            // $keywordPerformanceReportSort->SortColumn = CampaignPerformanceReportColumn::AccountId;
            // $keywordPerformanceReportSort->SortColumn = CampaignPerformanceReportColumn::AccountNumber;
            // $keywordPerformanceReportSort->SortColumn = CampaignPerformanceReportColumn::CampaignName;

            // $keywordPerformanceReportSort->SortOrder = SortOrder::Ascending;
            // $report->Sort[] = $keywordPerformanceReportSort;

            $encodedReport = new \SoapVar($report, SOAP_ENC_OBJECT, 'CampaignPerformanceReportRequest', $this->_reportingProxy->GetNamespace());

            return $this->downloadReport($encodedReport, $reportFile);
        } catch (SoapFault $e) {
            /*-- Output the last request/response --*/
            Log::error('[BingAdsLibrary] - Last SOAP request/response: ' .
                $this->_reportingProxy->GetWsdl() . "\n" .
                $this->_reportingProxy->GetService()->__getLastRequest() . "\n" .
                $this->_reportingProxy->GetService()->__getLastResponse());

            /*-- Reporting service operations can throw AdApiFaultDetail --*/
            if (isset($e->detail->AdApiFaultDetail)) {
                /*-- Log this fault --*/
                Log::error('[BingAdsLibrary] - The operation failed with the following faults: ');
                $errors = is_array($e->detail->AdApiFaultDetail->Errors->AdApiError)
                    ? $e->detail->AdApiFaultDetail->Errors->AdApiError
                    : array('AdApiError' => $e->detail->AdApiFaultDetail->Errors->AdApiError);
                /*-- If the AdApiError array is not null, the following are examples of error codes that may be found --*/
                foreach ($errors as $error) {
                    Log::error('[BingAdsLibrary][ERROR]: ' . json_encode($error));

                    switch ($error->Code) {
                        case 0:    // InternalError
                            break;
                        case 105:  // InvalidCredentials
                            break;
                        default:
                            Log::error('[BingAdsLibrary] - Please see MSDN documentation for more details about the error code output above.');
                            break;
                    }
                }
                throw new Exception($e);
            } elseif (isset($e->detail->ApiFaultDetail)) {
                /*-- Reporting service operations can throw ApiFaultDetail, log this fault --*/
                Log::error('[BingAdsLibrary] - The operation failed with the following faults: ');
                /*-- If the BatchError array is not null, the following are examples of error codes that may be found --*/
                if (!empty($e->detail->ApiFaultDetail->BatchErrors)) {
                    $errors = is_array($e->detail->ApiFaultDetail->BatchErrors->BatchError)
                        ? $e->detail->ApiFaultDetail->BatchErrors->BatchError
                        : array('BatchError' => $e->detail->ApiFaultDetail->BatchErrors->BatchError);
                    foreach ($errors as $error) {
                        Log::error('[BingAdsLibrary][ERROR][BATCH]: ' . json_error($error));

                        switch ($error->Code) {
                            case 0:     // InternalError
                                break;
                            default:
                                Log::error('[BingAdsLibrary] - Please see MSDN documentation for more details about the error code output above.');
                                break;
                        }
                    }
                }
                /*-- If the OperationError array is not null, the following are examples of error codes that may be found --*/
                if (!empty($e->detail->ApiFaultDetail->OperationErrors)) {
                    $errors = is_array($e->detail->ApiFaultDetail->OperationErrors->OperationError)
                        ? $e->detail->ApiFaultDetail->OperationErrors->OperationError
                        : array('OperationError' => $e->detail->ApiFaultDetail->OperationErrors->OperationError);
                    foreach ($errors as $error) {
                        Log::error('[BingAdsLibrary][ERROR][OPERATION]: ' . json_encode($error));

                        switch ($error->Code) {
                            case 0:     // InternalError
                                break;
                            case 106:   // UserIsNotAuthorized
                                break;
                            case 2100:  // ReportingServiceInvalidReportId
                                break;
                            default:
                                Log::error('[BingAdsLibrary] - Please see MSDN documentation for more details about the error code output above.');
                                break;
                        }
                    }
                }
                throw new Exception($e);
            }
        } catch (Exception $e) {
            if ($e->getPrevious()) {; // Ignore fault exceptions that we already caught.
            } else {
                Log::error('[BingAdsLibrary] - ' . $e->getCode() . ' - ' . $e->getMessage() . ' - ' . $e->getTraceAsString());
            }
            throw new Exception($e);
        }
        return false;
    }

    /**
     * @return $this|bool
     */
    private function getToken()
    {
        /*-- Get token from DB --*/
        $tokenInfo = BingAdsAccessToken::where('application', 'reporting')
            ->get(['refresh_token', 'access_token', 'expires_in', 'updated_at'])->first();

        /*-- If token is expired OR it will expire in 60sec, generate a new one --*/
        if (!empty($tokenInfo->refresh_token) && strtotime($tokenInfo->updated_at) + $tokenInfo->expires_in < time() - 60) {



            $options = [
                'form_params' => [
                    'client_id'     => $this->client_id,
                    'grant_type'    => 'refresh_token',
                    'scope'         => 'openid offline_access https://ads.microsoft.com/msads.manage',
                    'refresh_token' => $tokenInfo->refresh_token,
                    'client_secret' => $this->client_secret
                ],
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ],
                'debug' => false
            ];


            /*-- Send request for new refresh_token --*/
            try {
                $res = $this->client->post($this->url, $options);
            } catch (\GuzzleHttp\Exception\ClientException $e) {
                $response = $e->getResponse();
                $responseBodyAsString = $response->getBody()->getContents();
                $error = json_decode($responseBodyAsString);

                if (isset($error->error) && $error->error == 'invalid_grant' && strpos($error->error_description, 'The token was issued for a different client id') !== FALSE) {
                    Log::warning('[BingAdsLibrary][getToken]: (AccessToken on Database - Cleared - WIll be issued a new one at the next]): ' . $responseBodyAsString);
                } else {
                    Log::error('[BingAdsLibrary][getToken]: ' . $responseBodyAsString);
                }

                return false;
            }

            $tokenInfo = @json_decode($res->getBody());
            if (!$tokenInfo) {
                /*-- If nothing is returned from BingAds API, return false --*/
                Log::error('[BingAdsLibrary] - Nothing return from BingAds API');
                return false;
            }


            $bingAdsAccessToken = BingAdsAccessToken::where('application', 'reporting')
                ->update([
                    'access_token'  => $tokenInfo->access_token,
                    'expires_in'    => $tokenInfo->expires_in,
                    'id_token'      => $tokenInfo->id_token,
                    'refresh_token' => $tokenInfo->refresh_token,
                    'scope'         => $tokenInfo->scope,
                    'token_type'    => $tokenInfo->token_type
                ]);
        }

        /*-- Send authenticated token back --*/
        if (!empty($tokenInfo))
            return (new OAuthTokens())
                ->withAccessToken($tokenInfo->access_token)
                ->withRefreshToken($tokenInfo->refresh_token)
                ->withAccessTokenExpiresInSeconds($tokenInfo->expires_in);
        else return false;
    }

    /**
     * @return bool
     */
    public function isAuthenticated()
    {
        Log::info('[BingAdsLibrary] - Authenticating...');
        $token = $this->getToken();


        /*-- If not found any token, return false --*/
        if (empty($token)) {
            return false;
        }

        $this->authentication = (new OAuthWebAuthCodeGrant())
            ->withClientId($this->client_id)
            ->withClientSecret($this->client_secret)
            ->withEnvironment(ApiEnvironment::Production)
            ->withRedirectUri('')
            ->withOAuthTokens($token)
            ->withState(rand(0, 999999999));

        $this->authorizationData = (new AuthorizationData())
            ->withAuthentication($this->authentication)
            ->withDeveloperToken($this->developer_token);

        $this->authorizationData->CustomerId = $this->parent_customer_id;

        $this->_reportingProxy = new ServiceClient(ServiceClientType::ReportingVersion13, $this->authorizationData, ApiEnvironment::Production);
        $this->_customerProxy  = new ServiceClient(ServiceClientType::CustomerManagementVersion13, $this->authorizationData, ApiEnvironment::Production);
        $this->_campaignManagementProxy  = new ServiceClient(ServiceClientType::CampaignManagementVersion13, $this->authorizationData, ApiEnvironment::Production);
        


        /*-- Token's been found, return true --*/
        return true;
    }

    /**
     * @param $reportRequestId
     * @return mixed
     */
    private function pollGenerateReport($reportRequestId)
    {
        /*-- Set the request information --*/
        $request = new PollGenerateReportRequest();
        $request->ReportRequestId = $reportRequestId;

        return $this->_reportingProxy->GetService()->PollGenerateReport($request);
    }

    /**
     * @param $report
     * @return mixed
     */
    private function submitGenerateReport($report)
    {
        try {
            /*-- Set the request information --*/
            $request = new SubmitGenerateReportRequest();
            $request->ReportRequest = $report;

            return $this->_reportingProxy->GetService()->SubmitGenerateReport($request)->ReportRequestId;
        } catch (Exception $e) {
            /*-- Output the last request/response --*/
            // Log::error('[BingAdsLibrary][submitGenerateReport] - Last SOAP request/response: ' .
            //     $this->_reportingProxy->GetWsdl() . "\n" .
            //     $this->_reportingProxy->GetService()->__getLastRequest() . "\n" .
            //     $this->_reportingProxy->GetService()->__getLastResponse());

            /*-- Reporting service operations can throw AdApiFaultDetail --*/
            if (isset($e->detail->AdApiFaultDetail)) {
                /*-- Log this fault --*/
                Log::error('[BingAdsLibrary][submitGenerateReport] - The operation failed with the following faults:');
                $errors = is_array($e->detail->AdApiFaultDetail->Errors->AdApiError)
                    ? $e->detail->AdApiFaultDetail->Errors->AdApiError
                    : array('AdApiError' => $e->detail->AdApiFaultDetail->Errors->AdApiError);
                /*-- If the AdApiError array is not null, the following are examples of error codes that may be found --*/
                foreach ($errors as $error) {
                    Log::error('[BingAdsLibrary][ERROR][API]: ' . json_encode($error));

                    switch ($error->Code) {
                        case 0:    // InternalError
                            break;
                        case 105:  // InvalidCredentials
                            break;
                        default:
                            Log::error('[BingAdsLibrary][submitGenerateReport] - Please see MSDN documentation for more details about the error code output above.');
                            break;
                    }
                }
            } elseif (isset($e->detail->ApiFaultDetail)) {
                /*-- Reporting service operations can throw ApiFaultDetail, log this fault --*/
                Log::error('[BingAdsLibrary][submitGenerateReport] - The operation failed with the following faults:');
                /*-- If the BatchError array is not null, the following are examples of error codes that may be found --*/
                if (!empty($e->detail->ApiFaultDetail->BatchErrors)) {
                    $errors = is_array($e->detail->ApiFaultDetail->BatchErrors->BatchError)
                        ? $e->detail->ApiFaultDetail->BatchErrors->BatchError
                        : array('BatchError' => $e->detail->ApiFaultDetail->BatchErrors->BatchError);
                    foreach ($errors as $error) {
                        Log::error('[BingAdsLibrary][ERROR][BATCH]: ' . json_encode($error));

                        switch ($error->Code) {
                            case 0:     // InternalError
                                break;
                            default:
                                Log::error('[BingAdsLibrary][submitGenerateReport] - Please see MSDN documentation for more details about the error code output above.');
                                break;
                        }
                    }
                }
                /*-- If the OperationError array is not null, the following are examples of error codes that may be found --*/
                if (!empty($e->detail->ApiFaultDetail->OperationErrors)) {
                    $errors = is_array($e->detail->ApiFaultDetail->OperationErrors->OperationError)
                        ? $e->detail->ApiFaultDetail->OperationErrors->OperationError
                        : array('OperationError' => $e->detail->ApiFaultDetail->OperationErrors->OperationError);
                    foreach ($errors as $error) {
                        Log::error('[BingAdsLibrary][submitGenerateReport][ERROR][Operation]:: ' . json_encode($error));
                        // switch ($error->Code) {
                        //     case 0:     // InternalError
                        //         break;
                        //     case 106:   // UserIsNotAuthorized
                        //         break;
                        //     case 2100:  // ReportingServiceInvalidReportId
                        //         break;
                        //     default:
                        //         Log::error('[BingAdsLibrary][submitGenerateReport] - Please see MSDN documentation for more details about the error code output above.');
                        //         break;
                        // }
                    }
                }
            }

            return false;
        }
    }
}
