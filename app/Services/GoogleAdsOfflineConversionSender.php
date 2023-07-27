<?php

namespace App\Services;



use Google\Ads\GoogleAds\Lib\Configuration;
use Google\Ads\GoogleAds\Lib\OAuth2TokenBuilder;
use Google\Ads\GoogleAds\Lib\V13\GoogleAdsClient;
use Google\Ads\GoogleAds\Lib\V13\GoogleAdsClientBuilder;
use Google\Ads\GoogleAds\Lib\V13\GoogleAdsException;
use Google\Ads\GoogleAds\Util\V13\ResourceNames;
use Google\Ads\GoogleAds\V13\Errors\GoogleAdsError;
use Google\Ads\GoogleAds\V13\Services\ClickConversion;
use Google\Ads\GoogleAds\V13\Services\ClickConversionResult;
use Google\Ads\GoogleAds\V13\Services\CustomVariable;
use Google\Ads\GoogleAds\V13\Services\UploadClickConversionsResponse;
use Google\ApiCore\ApiException;

use Illuminate\Support\Facades\Log;

use Exception;


class GoogleAdsOfflineConversionSender
{
    protected $googleAdsClient;
    protected $oAuth2Credential;
    protected $logged_in = false;
    protected $gads_array_configuration;
    protected $gads_configuration;

    protected $conversion_id = '6492772042';
    protected $print_to_csv = false;

    protected $errors = [];

    public function __construct($conversion_id = '')
    {
        $this->login();
    }


    public function login()
    {
        $this->gads_array_configuration = $this->createIniFromConfig();
        $this->gads_configuration = new Configuration($this->gads_array_configuration);

        // Generate a refreshable OAuth2 credential for authentication.
        try {
            $this->oAuth2Credential = (new OAuth2TokenBuilder())
                ->from($this->gads_configuration)
                ->build();


            $this->googleAdsClient = (new GoogleAdsClientBuilder())
                ->from($this->gads_configuration)
                ->withOAuth2Credential($this->oAuth2Credential)
                ->build();

            $this->logged_in = true;
        } catch (OAuth2Exception $e) {
            return $this->CheckForOAuth2Errors($e);
        } catch (ValidationException $e) {
            return $this->CheckForOAuth2Errors($e);
        } catch (Exception $e) {
            Log::error("[GoogleAdsOfflineConversionSender] An error has occurred: " . $e->getMessage());
            return false;
        }
    }

    public function getConversionId()
    {
        return $this->conversion_id;
    }

    public function getConversionResourceName($account_id)
    {
        return ResourceNames::forConversionAction(
            str_replace('-', '', $account_id),
            $this->conversion_id
        );
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function isAuthenticated()
    {
        return $this->logged_in;
    }

    public function sendConversions($account_id, $conversions)
    {
        $account_id = str_replace('-', '', $account_id);
        $this->errors = [];
        $offlineConversions = [];
        foreach ($conversions as $conv) {

            $offlineConvData = [
                'conversion_action' => $this->getConversionResourceName($account_id),
                //'gclid' => $conv['clid'],
                'conversion_value' => $conv['revenue'],
                'conversion_date_time' => $conv['datetime'],
                'currency_code' => $conv['currency']
            ];

            if (!empty($conv['gclid'])) {
                $offlineConvData['gclid'] = $conv['gclid'];
            } elseif (!empty($conv['gbraid'])) {
                $offlineConvData['gbraid'] = $conv['gbraid'];
            } elseif (!empty($conv['wbraid'])) {
                $offlineConvData['wbraid'] = $conv['wbraid'];
            } else {
                $offlineConvData['gclid'] = $conv['clid'];
            }

            // if($this->print_to_csv) {
            //         file_put_contents('/tmp/'. $account_id.'.csv', $this->str_putcsv($offlineConvData)."\n", FILE_APPEND);
            // }

            //Log::info('[GoogleAdsOfflineConversionSender][offlineConvData]: ' .json_encode($offlineConvData));
            $offlineConversion = new ClickConversion($offlineConvData);
            $offlineConversions[] = $offlineConversion;
        }



        // if($this->print_to_csv) return true;

        try {

            // Issues a request to upload the click conversion.
            $conversionUploadServiceClient = $this->googleAdsClient->getConversionUploadServiceClient();

            /** @var UploadClickConversionsResponse $response */
            $response = $conversionUploadServiceClient->uploadClickConversions(
                $this->gads_array_configuration['GOOGLE_ADS']['loginCustomerId'], //$account_id,
                $offlineConversions,
                true
            );

            // Prints the status message if any partial failure error is returned.
            // Note: The details of each partial failure error are not printed here, you can refer to
            // the example HandlePartialFailure.php to learn more.
            if (!is_null($response->getPartialFailureError())) {
                $this->errors[] = $response->getPartialFailureError()->getMessage();
                return false;
            }

            return true;
        } catch (GoogleAdsException $googleAdsException) {
            foreach ($googleAdsException->getGoogleAdsFailure()->getErrors() as $error) {
                /** @var GoogleAdsError $error */
                $this->errors[] = sprintf(
                    "\t%s: %s%s",
                    $error->getErrorCode()->getErrorCode(),
                    $error->getMessage(),
                    PHP_EOL
                );
            }
            return false;
        } catch (ApiException $apiException) {
            $this->errors[] = sprintf(
                "ApiException was thrown with message '%s'",
                $apiException->getMessage()
            );
            return false;
        }

        return true;
    }


    public function CheckForOAuth2Errors(Exception $raisedException)
    {
        $errorMessage = "[ERROR] An error has occured:";
        if ($raisedException instanceof OAuth2Exception) {
            $errorMessage = "Your OAuth2 Credentials are incorrect.\nPlease see the GetRefreshToken.php example.";
        } elseif ($raisedException instanceof ValidationException) {
            $requiredAuthFields = array('client_id', 'client_secret', 'refresh_token');
            $trigger = $raisedException->GetTrigger();
            if (in_array($trigger, $requiredAuthFields)) {
                $errorMessage = sprintf("Your OAuth2 Credentials are missing the '%s'.\nPlease see GetRefreshToken.php for further information.", $trigger);
            }
        }
        $this->errors[] = $errorMessage;
        Log::error('[GoogleAdsOfflineConversionSender]' . $errorMessage . "; " . $raisedException->getMessage());
        return false;
    }


    private function createIniFromConfig($params = [])
    {
        if (empty($params)) {
            $params = [
                'clientCustomerId' => config('arc.sources.googleads.clientCustomerId'),
                'developerToken'   => config('arc.sources.googleads.developerToken'),
                'clientId'     => config('arc.sources.googleads.clientId'),
                'clientSecret' => config('arc.sources.googleads.clientSecret'),
                'refreshToken' => config('arc.sources.googleads.refreshToken')
            ];
        }
        return [
            'GOOGLE_ADS' => [
                'loginCustomerId' => str_replace('-', '', $params['clientCustomerId']),
                'developerToken'   => $params['developerToken'],
            ],
            'OAUTH2' => [
                'clientId'     => $params['clientId'],
                'clientSecret' => $params['clientSecret'],
                'refreshToken' => $params['refreshToken'],
            ]
        ];
    }

    protected function str_putcsv($input, $delimiter = ',', $enclosure = '"')
    {
        $fp = fopen('php://memory', 'r+b');
        fputcsv($fp, $input, $delimiter, $enclosure);
        rewind($fp);
        $data = rtrim(stream_get_contents($fp), "\n");
        fclose($fp);
        return $data;
    }
}
