<?php

namespace App\Services;

use App\Services\ARC\Sources\Providers\BingAds\BingAdsLibrary;

// Specify the Microsoft\BingAds\V13\AdInsight classes that will be used.
use Microsoft\BingAds\V13\AdInsight\GetKeywordIdeasRequest;
use Microsoft\BingAds\V13\AdInsight\KeywordIdeaAttribute;
use Microsoft\BingAds\V13\AdInsight\SearchParameter;
use Microsoft\BingAds\V13\AdInsight\DateRangeSearchParameter;
use Microsoft\BingAds\V13\AdInsight\DayMonthAndYear;
use Microsoft\BingAds\V13\AdInsight\CategorySearchParameter;
use Microsoft\BingAds\V13\AdInsight\QuerySearchParameter;
use Microsoft\BingAds\V13\AdInsight\UrlSearchParameter;
use Microsoft\BingAds\V13\AdInsight\LanguageSearchParameter;
use Microsoft\BingAds\V13\AdInsight\Criterion;
use Microsoft\BingAds\V13\AdInsight\LanguageCriterion;
use Microsoft\BingAds\V13\AdInsight\LocationSearchParameter;
use Microsoft\BingAds\V13\AdInsight\LocationCriterion;
use Microsoft\BingAds\V13\AdInsight\NetworkSearchParameter;
use Microsoft\BingAds\V13\AdInsight\NetworkCriterion;
use Microsoft\BingAds\V13\AdInsight\NetworkType;
use Microsoft\BingAds\V13\AdInsight\CompetitionSearchParameter;
use Microsoft\BingAds\V13\AdInsight\CompetitionLevel;
use Microsoft\BingAds\V13\AdInsight\ExcludeAccountKeywordsSearchParameter;
use Microsoft\BingAds\V13\AdInsight\IdeaTextSearchParameter;
use Microsoft\BingAds\V13\AdInsight\Keyword;
use Microsoft\BingAds\V13\AdInsight\MatchType;
use Microsoft\BingAds\V13\AdInsight\ImpressionShareSearchParameter;
use Microsoft\BingAds\V13\AdInsight\SearchVolumeSearchParameter;
use Microsoft\BingAds\V13\AdInsight\SuggestedBidSearchParameter;
use Microsoft\BingAds\V13\AdInsight\DeviceSearchParameter;
use Microsoft\BingAds\V13\AdInsight\DeviceCriterion;
use Microsoft\BingAds\V13\AdInsight\AdGroupEstimator;
use Microsoft\BingAds\V13\AdInsight\KeywordEstimator;
use Microsoft\BingAds\V13\AdInsight\CampaignEstimator;
use Microsoft\BingAds\V13\AdInsight\AdGroupEstimate;
use Microsoft\BingAds\V13\AdInsight\KeywordEstimate;
use Microsoft\BingAds\V13\AdInsight\CampaignEstimate;
use Microsoft\BingAds\V13\AdInsight\NegativeKeyword;

use Microsoft\BingAds\Auth\ApiEnvironment;
use Microsoft\BingAds\Auth\ServiceClient;
use Microsoft\BingAds\Auth\ServiceClientType;

use SoapVar;
use SoapFault;
use Exception;
use DateTime;
use Log;

class BingAdsKeywordPlanner extends BingAdsLibrary
{

    protected $_adInsightProxy  = null;


    public function buildAdInsightProxy($account_id)
    {
        $this->authorizationData->AccountId = $account_id;
        $this->_adInsightProxy  = new ServiceClient(ServiceClientType::AdInsightVersion13, $this->authorizationData, ApiEnvironment::Production);
    }

    public function getKeywordIdeas($keyword, $location_id, $language)
    {
        $ideaAttributes = array();
        $ideaAttributes[] = KeywordIdeaAttribute::AdGroupId;
        $ideaAttributes[] = KeywordIdeaAttribute::AdGroupName;
        $ideaAttributes[] = KeywordIdeaAttribute::AdImpressionShare;
        $ideaAttributes[] = KeywordIdeaAttribute::Competition;
        $ideaAttributes[] = KeywordIdeaAttribute::Keyword;
        $ideaAttributes[] = KeywordIdeaAttribute::MonthlySearchCounts;
        $ideaAttributes[] = KeywordIdeaAttribute::Relevance;
        $ideaAttributes[] = KeywordIdeaAttribute::Source;
        $ideaAttributes[] = KeywordIdeaAttribute::SuggestedBid;

        date_default_timezone_set('UTC');
        $now = new DateTime(gmdate('Y-m-d H:i:s', time()));

        // Only one of each SearchParameter type can be specified per call. 

        $searchParameters = array();

        $dateRangeSearchParameter = new DateRangeSearchParameter();
        $dateRangeSearchParameterEndDate = new DayMonthAndYear();
        $dateRangeSearchParameterEndDate->Day = 1;
        $dateRangeSearchParameterEndDate->Month = 8;
        $dateRangeSearchParameterEndDate->Year = 2022;
        $dateRangeSearchParameter->EndDate = $dateRangeSearchParameterEndDate;
        $dateRangeSearchParameterStartDate = new DayMonthAndYear();
        $dateRangeSearchParameterStartDate->Day = 1;
        $dateRangeSearchParameterStartDate->Month = 6;
        $dateRangeSearchParameterStartDate->Year = 2022;
        $dateRangeSearchParameter->StartDate = $dateRangeSearchParameterStartDate;
        $searchParameters[] = new SoapVar(
            $dateRangeSearchParameter,
            SOAP_ENC_OBJECT,
            'DateRangeSearchParameter',
            $this->_adInsightProxy->GetNamespace()
        );

        $querySearchParameter = new QuerySearchParameter();
        $querySearchParameter->Queries = array($keyword);
        $searchParameters[] = new SoapVar(
            $querySearchParameter,
            SOAP_ENC_OBJECT,
            'QuerySearchParameter',
            $this->_adInsightProxy->GetNamespace()
        );

        // The LanguageSearchParameter, LocationSearchParameter, and NetworkSearchParameter
        // correspond to the 'Keyword Planner' -> 'Search for new keywords using a phrase, website, or category' ->
        // 'Targeting' workflow in the Bing Ads web application.
        // Each of these search parameters are required.

        $languageSearchParameter = new LanguageSearchParameter();
        $languageCriterion = new LanguageCriterion();
        $languageCriterion->Language = $language;
        // You must specify exactly one language
        $languageSearchParameter->Languages[] = new SoapVar(
            $languageCriterion,
            SOAP_ENC_OBJECT,
            'LanguageCriterion',
            $this->_adInsightProxy->GetNamespace()
        );
        $searchParameters[] = new SoapVar(
            $languageSearchParameter,
            SOAP_ENC_OBJECT,
            'LanguageSearchParameter',
            $this->_adInsightProxy->GetNamespace()
        );

        $locationSearchParameter = new LocationSearchParameter();
        $locationSearchParameter->Locations = array();
        $locationCriterion = new LocationCriterion();
        // United States
        $locationCriterion->LocationId = $location_id;
        // You must specify between 1 and 100 locations
        $locationSearchParameter->Locations[] = new SoapVar(
            $locationCriterion,
            SOAP_ENC_OBJECT,
            'LocationCriterion',
            $this->_adInsightProxy->GetNamespace()
        );
        $searchParameters[] = new SoapVar(
            $locationSearchParameter,
            SOAP_ENC_OBJECT,
            'LocationSearchParameter',
            $this->_adInsightProxy->GetNamespace()
        );


        $competitionSearchParameter = new CompetitionSearchParameter();
        $competitionLevels = array(CompetitionLevel::High);
        $competitionSearchParameter->CompetitionLevels = $competitionLevels;
        $searchParameters[] = new SoapVar(
            $competitionSearchParameter,
            SOAP_ENC_OBJECT,
            'CompetitionSearchParameter',
            $this->_adInsightProxy->GetNamespace()
        );

        $searchVolumeSearchParameter = new SearchVolumeSearchParameter();
        // Equivalent of 'value >= 50'
        $searchVolumeSearchParameter->Maximum = null;
        $searchVolumeSearchParameter->Minimum = 1000;
        $searchParameters[] = new SoapVar(
            $searchVolumeSearchParameter,
            SOAP_ENC_OBJECT,
            'SearchVolumeSearchParameter',
            $this->_adInsightProxy->GetNamespace()
        );


        $networkSearchParameter = new NetworkSearchParameter();
        $networkCriterion = new NetworkCriterion();
        $networkCriterion->Network = NetworkType::OwnedAndOperatedAndSyndicatedSearch;
        $networkSearchParameter->Network = new SoapVar(
            $networkCriterion,
            SOAP_ENC_OBJECT,
            'NetworkCriterion',
            $this->_adInsightProxy->GetNamespace()
        );
        $searchParameters[] = new SoapVar(
            $networkSearchParameter,
            SOAP_ENC_OBJECT,
            'NetworkSearchParameter',
            $this->_adInsightProxy->GetNamespace()
        );


        $expandIdeas = true;
        $request = new GetKeywordIdeasRequest();

        $request->ExpandIdeas = $expandIdeas;
        $request->IdeaAttributes = $ideaAttributes;
        $request->SearchParameters = $searchParameters;

        try {
            $response = $this->_adInsightProxy->GetService()->GetKeywordIdeas($request);
            $tmp = [];
    
            if(!empty($response->KeywordIdeas)) {
                foreach($response->KeywordIdeas->KeywordIdea as $keywordIdea) {
                    
                    if($keywordIdea->Source == 'Seed') continue;

                    $tmp[] = [
                        'keyword' => $keywordIdea->Keyword,
                        'suggested_bid' => $keywordIdea->SuggestedBid
                    ];
                }
            }

            return $tmp;
        } catch (SoapFault $e) {
            /*-- Reporting service operations can throw AdApiFaultDetail --*/
            if (isset($e->detail->AdApiFaultDetail)) {
                /*-- Log this fault --*/
                Log::error('[BingAdsKeywordPlanner] - The operation failed with the following faults:');

                $errors = is_array($e->detail->AdApiFaultDetail->Errors->AdApiError)
                    ? $e->detail->AdApiFaultDetail->Errors->AdApiError
                    : array('AdApiError' => $e->detail->AdApiFaultDetail->Errors->AdApiError);

                /*-- If the AdApiError array is not null, the following are examples of error codes that may be found --*/
                foreach ($errors as $error) {
                    Log::error('[BingAdsKeywordPlanner][ERROR]: ' . json_encode($error));
                    switch ($error->Code) {
                        case 0:    // InternalError
                            break;
                        case 105:  // InvalidCredentials
                            break;
                        default:
                            Log::error('[BingAdsKeywordPlanner] - Please see MSDN documentation for more details about the error code output above.');
                            break;
                    }
                }
                return false;
            } elseif (isset($e->detail->ApiFaultDetail)) {
                /*-- Reporting service operations can throw ApiFaultDetail, log this fault --*/
                Log::error('[BingAdsKeywordPlanner] - The operation failed with the following faults:');
                /*-- If the BatchError array is not null, the following are examples of error codes that may be found --*/
                if (!empty($e->detail->ApiFaultDetail->BatchErrors)) {
                    $errors = is_array($e->detail->ApiFaultDetail->BatchErrors->BatchError)
                        ? $e->detail->ApiFaultDetail->BatchErrors->BatchError
                        : array('BatchError' => $e->detail->ApiFaultDetail->BatchErrors->BatchError);
                    foreach ($errors as $error) {
                        Log::error('[BingAdsKeywordPlanner][ERROR][BATCH]: ' . json_encode($error));

                        switch ($error->Code) {
                            case 0:     // InternalError
                                break;
                            default:
                                Log::error('[BingAdsKeywordPlanner] - Please see MSDN documentation for more details about the error code output above.');
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
                        Log::error('[BingAdsKeywordPlanner][ERROR][OPERATION]: ' . json_encode($error));

                        switch ($error->Code) {
                            case 0:     // InternalError
                                break;
                            case 106:   // UserIsNotAuthorized
                                break;
                            case 2100:  // ReportingServiceInvalidReportId
                                break;
                            default:
                                Log::error('[BingAdsKeywordPlanner] - Please see MSDN documentation for more details about the error code output above.');
                                break;
                        }
                    }
                    return false;
                }
            }
        } catch (Exception $e) {
            if ($e->getPrevious()) {; // Ignore fault exceptions that we already caught.
            } else {
                Log::error('[BingAdsKeywordPlanner] - ' . $e->getCode() . ' - ' . $e->getMessage() . ' - ' . $e->getTraceAsString());
            }
            return false;
        }
        return [];
    }
}
