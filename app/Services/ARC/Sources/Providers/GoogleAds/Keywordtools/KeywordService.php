<?php

namespace App\Services\ARC\Sources\Providers\GoogleAds\Keywordtools;

use App\Services\ARC\Sources\Providers\GoogleAds\GoogleAdsLibrary;
use Google\Ads\GoogleAds\Util\V13\ResourceNames;
use Google\ApiCore\ApiException;
use Google\Ads\GoogleAds\Lib\V13\GoogleAdsException;
use Google\Ads\GoogleAds\V13\Enums\MonthOfYearEnum\MonthOfYear;
use Google\Ads\GoogleAds\V13\Enums\KeywordPlanNetworkEnum\KeywordPlanNetwork;
use Google\Ads\GoogleAds\V13\Enums\KeywordPlanCompetitionLevelEnum\KeywordPlanCompetitionLevel;
use Google\Ads\GoogleAds\V13\Services\KeywordSeed;

class KeywordService
{

    private $customerId = '5896515798';

    public function getKeywordIdeas($keywords, $countryId)
    {

        $googleAdsLibrary = new GoogleAdsLibrary();
        $googleAdsClient = $googleAdsLibrary->getGoogleAdsClient();
        $errors = [];
        try {

            if (empty($keywords)) {
                $errors[] = 'At least one of keywords or page URL is required, but neither was specified';
                return false;
            }

            $locationIds = [$countryId];

            $geoTargetConstants = $countryId ? array_map(function ($locationId) {
                return ResourceNames::forGeoTargetConstant($locationId);
            }, $locationIds) : [];



            $keywordPlanIdeaServiceClient = $googleAdsClient->getKeywordPlanIdeaServiceClient();

            $requestOptionalArgs['keywordSeed'] = new KeywordSeed(['keywords' => $keywords]);

            $response = $keywordPlanIdeaServiceClient->generateKeywordIdeas(
                [
                    'customerId' => '5896515798',
                    // Add the resource name of each location ID to the request.
                    'geoTargetConstants' => $geoTargetConstants,
                    // Set the network. To restrict to only Google Search, change the parameter below to
                    // KeywordPlanNetwork::GOOGLE_SEARCH.
                    'keywordPlanNetwork' => KeywordPlanNetwork::GOOGLE_SEARCH_AND_PARTNERS,

                ] + $requestOptionalArgs
            );


            $tmp = [];

            // Iterate over the results and print its detail.
            foreach ($response->iterateAllElements() as $result) {


                $el = [
                    'keyword' => $result->getText(),
                    'avg_monthly_searches' =>  is_null($result->getKeywordIdeaMetrics()) ?
                        0 : $result->getKeywordIdeaMetrics()->getAvgMonthlySearches(),
                    'competition' => is_null($result->getKeywordIdeaMetrics()) ?
                        0 : $result->getKeywordIdeaMetrics()->getCompetition(),
                    'competition_index' => is_null($result->getKeywordIdeaMetrics()) ?
                        0 : $result->getKeywordIdeaMetrics()->getCompetitionIndex(),
                    'low_top_of_page_bid_micros' => is_null($result->getKeywordIdeaMetrics()) ?
                        0 : $result->getKeywordIdeaMetrics()->getLowTopOfPageBidMicros() ?? 0,
                    'high_top_of_page_bid_micros' => is_null($result->getKeywordIdeaMetrics()) ?
                        0 : $result->getKeywordIdeaMetrics()->getHighTopOfPageBidMicros() ?? 0
                ];
                $el['competition_label'] = KeywordPlanCompetitionLevel::name($el['competition']);

                if (!is_null($result->getKeywordIdeaMetrics())) {
                    foreach ($result->getKeywordIdeaMetrics()->getMonthlySearchVolumes() as $volume) {
                        $el['monthly_search_volumes'][] = [
                            'avg_monthly_searches' => $volume->getMonthlySearches(),
                            'year' => $volume->getYear(),
                            'month' => MonthOfYear::name($volume->getMonth())
                        ];
                    }
                }

                $el['low_top_of_page_bid'] = is_null($el['low_top_of_page_bid_micros']) ? 0 : $this->convertFromMicros($el['low_top_of_page_bid_micros']);
                $el['high_top_of_page_bid'] = is_null($el['high_top_of_page_bid_micros']) ? 0 : $this->convertFromMicros($el['high_top_of_page_bid_micros']);
                $tmp[] = $el;
            }

            return ['keywords' => $tmp];
        } catch (GoogleAdsException $googleAdsException) {

            foreach ($googleAdsException->getGoogleAdsFailure()->getErrors() as $error) {
                $errors[] = $error;
            }
            return  $errors;
        } catch (ApiException $apiException) {
            printf(
                "ApiException was thrown with message '%s'.%s",
                $apiException->getMessage(),
                PHP_EOL
            );
            $errors[] = "ApiException was thrown with message '" . $apiException->getMessage() . "'";
            return $errors;
        }
    }

    public function getKeywordStats($keywords = [], $countryId)
    {
        $errors = [];
        try {

            $googleAdsLibrary = new GoogleAdsLibrary();
            $googleAdsClient = $googleAdsLibrary->getGoogleAdsClient();
            $keywordPlanServiceClient = $googleAdsClient->getKeywordPlanIdeaServiceClient();
            //$requestOptionalArgs['keywordSeed'] = new KeywordSeed(['keywords' => $keywords]);

            $locationIds = [$countryId];

            $geoTargetConstants = $countryId ? array_map(function ($locationId) {
                return ResourceNames::forGeoTargetConstant($locationId);
            }, $locationIds) : [];


            // Generate keyword ideas based on the specified parameters.
            $response = $keywordPlanServiceClient->GenerateKeywordHistoricalMetrics(
                [
                    'keywords' => $keywords,
                    'customerId' => $this->customerId,
                    // Add the resource name of each location ID to the request.
                    'geoTargetConstants' => $geoTargetConstants,
                    // Set the network. To restrict to only Google Search, change the parameter below to
                    // KeywordPlanNetwork::GOOGLE_SEARCH.
                    'keywordPlanNetwork' => KeywordPlanNetwork::GOOGLE_SEARCH_AND_PARTNERS
                ]
            );


            $tmp = [];
            // Iterate over the results and print its detail.

            foreach ($response->getResults() as $result) {

                $el = [
                    'keyword' => $result->getText(),
                    'avg_monthly_searches' => is_null($result->getKeywordMetrics()) ?
                        0 : $result->getKeywordMetrics()->getAvgMonthlySearches(),
                    'monthly_search_volumes' => [],
                    'competition' => is_null($result->getKeywordMetrics()) ?
                        0 : $result->getKeywordMetrics()->getCompetition(),
                    'competition_index' => is_null($result->getKeywordMetrics()) ?
                        0 : $result->getKeywordMetrics()->getCompetitionIndex(),
                    'low_top_of_page_bid_micros' => is_null($result->getKeywordMetrics()) ?
                        0 : $result->getKeywordMetrics()->getLowTopOfPageBidMicros() ?? 0,
                    'high_top_of_page_bid_micros' => is_null($result->getKeywordMetrics()) ?
                        0 : $result->getKeywordMetrics()->getHighTopOfPageBidMicros() ?? 0
                ];

                $el['competition_label'] = KeywordPlanCompetitionLevel::name($el['competition']);

                foreach ($result->getCloseVariants() as $variant) {
                    $el['variants'][] = (string) $variant;
                }


                if (!is_null($result->getKeywordMetrics())) {
                    foreach ($result->getKeywordMetrics()->getMonthlySearchVolumes() as $volume) {
                        $el['monthly_search_volumes'][] = [
                            'avg_monthly_searches' => $volume->getMonthlySearches(),
                            'year' => $volume->getYear(),
                            'month' => MonthOfYear::name($volume->getMonth())
                        ];
                    }
                }



                $el['low_top_of_page_bid'] = is_null($el['low_top_of_page_bid_micros']) ? 0 : $this->convertFromMicros($el['low_top_of_page_bid_micros']);
                $el['high_top_of_page_bid'] = is_null($el['high_top_of_page_bid_micros']) ? 0 : $this->convertFromMicros($el['high_top_of_page_bid_micros']);
                $tmp[] = $el;
            }
            return ['keywords' => $tmp];
        } catch (GoogleAdsException $googleAdsException) {

            foreach ($googleAdsException->getGoogleAdsFailure()->getErrors() as $error) {
                $errors[] = $error;
            }
            return $errors;
        } catch (ApiException $apiException) {
            printf(
                "ApiException was thrown with message '%s'.%s",
                $apiException->getMessage(),
                PHP_EOL
            );
            $errors[] = "ApiException was thrown with message '" . $apiException->getMessage() . "'";
            return $errors;
        }
    }

    public function convertFromMicros($number)
    {
        return $number / 1000000;
    }
}
