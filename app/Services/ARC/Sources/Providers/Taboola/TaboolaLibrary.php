<?php

namespace App\Services\ARC\Sources\Providers\Taboola;

# Including Villaflor Taboola Client
use Villaflor\Connection\Auth\APIToken;
use Villaflor\Connection\Auth\None;
use Villaflor\TaboolaSDK\Configurations\Account\AccountDetailsConfiguration;
use Villaflor\TaboolaSDK\Configurations\Account\AdvertiserAccountsInNetworkConfiguration;
use Villaflor\TaboolaSDK\Configurations\Account\AllowedAccountsConfiguration;
use Villaflor\TaboolaSDK\Configurations\AuthenticationConfiguration;
use Villaflor\TaboolaSDK\Configurations\Campaigns\AllCampaignsConfiguration;
use Villaflor\TaboolaSDK\Configurations\Reporting\CampaignSummaryConfiguration;
use Villaflor\TaboolaSDK\Configurations\Reporting\TopCampaignContentConfiguration;
use Villaflor\TaboolaSDK\Definitions\AllCampaignsFilterDefinition;
use Villaflor\TaboolaSDK\Definitions\CampaignSummaryDimensionDefinition;
use Villaflor\TaboolaSDK\Definitions\CampaignSummaryFilterDefinition;
use Villaflor\TaboolaSDK\Definitions\TopCampaignContentDimensionDefinition;
use Villaflor\TaboolaSDK\Definitions\TopCampaignContentFilterDefinition;
use Villaflor\TaboolaSDK\Endpoints\Account;
use Villaflor\TaboolaSDK\Endpoints\Authentication;
use Villaflor\TaboolaSDK\Endpoints\Campaigns;
use Villaflor\TaboolaSDK\Endpoints\Reporting;
use Villaflor\TaboolaSDK\TaboolaClient;


class TaboolaLibrary
{
	protected $clientId = null;
	protected $clientSecret = null;
	protected $accountId = null;

	private $auth = null;
	private $accessToken = null;
	private $service = null;
	private $config = null;
	private $taboolaClient = null;


	public function __construct($clientId, $clientSecret)
	{
		$this->clientId = $clientId;
		$this->clientSecret = $clientSecret;
	}

	public function setAccountId($accountId)
	{
		$this->accountId = $accountId;
	}

	protected function access()
	{
		if ($this->accessToken == null) {
			$this->accessToken = $this->getAccessToken();
		}
	}

	private function getService()
	{
		if ($this->service == null) {
			$this->service = new Authentication(new TaboolaClient(new None()));
		}
		return $this->service;
	}

	private function getConfig()
	{
		if ($this->config == null) {
			$this->config = new AuthenticationConfiguration($this->clientId, $this->clientSecret);
		}
		return $this->config;
	}

	private function getAuth()
	{
		if ($this->auth == null) {
			$this->auth = new APIToken($this->getAccessToken());
		}
		return $this->auth;
	}

	private function getTaboolaClient()
	{
		if ($this->taboolaClient == null) {
			$this->taboolaClient = new TaboolaClient($this->getAuth());
		}
		return $this->taboolaClient;
	}


	private function getAccessToken()
	{
		$service = $this->getService();
		$config = $this->getConfig();
		$result = $service->getAccessToken($config);
		return $result->body->access_token;
	}

	public function getAccountDetails()
	{
		$service = new Account($this->getTaboolaClient());
		$config = new AccountDetailsConfiguration();
		return $service->getAccountDetails($config);
	}

	public function getAdvertiserAccountsInNetwork()
	{
		$service = new Account($this->getTaboolaClient());
		$config = new AdvertiserAccountsInNetworkConfiguration($this->accountId);
		return $service->getAdvertiserAccountsInNetwork($config);
	}

	public function getAllowedAccounts()
	{
		$service = new Account($this->getTaboolaClient());
		$config = new AllowedAccountsConfiguration();
		return $service->getAllowedAccounts($config);
	}

	public function getAllCampaigns()
	{
		$service = new Campaigns($this->getTaboolaClient());
		$config = new AllCampaignsConfiguration($this->accountId, [AllCampaignsFilterDefinition::FETCH_LEVEL => AllCampaignsFilterDefinition::FETCH_LEVEL_RECENT_AND_PAUSED_OPTIONS]);
		return $service->getAllCampaigns($config);
	}

	public function getCampaignSummaryReport($start_date, $end_date)
	{
		$service = new Reporting($this->getTaboolaClient());
		$config = new CampaignSummaryConfiguration(
			$this->accountId,
			CampaignSummaryDimensionDefinition::CAMPAIGN_DAY_BREAKDOWN,
			[
				CampaignSummaryFilterDefinition::START_DATE => $start_date,
				CampaignSummaryFilterDefinition::END_DATE => $end_date,
			]
		);
		return $service->getCampaignSummaryReport($config);
	}

	private function getTopCampaignContentReport($start_date, $end_date)
	{
		$service = new Reporting($this->getTaboolaClient());
		$config = new TopCampaignContentConfiguration(
			$this->accountId,
			TopCampaignContentDimensionDefinition::ITEM_BREAKDOWN,
			[
				TopCampaignContentFilterDefinition::START_DATE => $start_date,
				TopCampaignContentFilterDefinition::END_DATE => $end_date,
			]
		);
		return $service->getTopCampaignContentReport($config);
	}
}
