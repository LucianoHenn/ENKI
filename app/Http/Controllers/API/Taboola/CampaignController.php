<?php

namespace App\Http\Controllers\API\Taboola;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController;
use App\Services\APIs\TaboolaApi;
use App\Services\ARC\Sources\Providers\Taboola\TaboolaLibrary;
use Illuminate\Http\Client\RequestException;


class CampaignController extends BaseController
{
    public function index(Request $request)
    {

        $client_id  =  config('arc.sources.taboola.client_id');
        $client_secret = config('arc.sources.taboola.client_secret');

        $tClient = new TaboolaLibrary($client_id, $client_secret);
        $tClient->setAccountId('bidberrysrl-sy1-localservices-sc');
        $campaigns = $tClient->getAllCampaigns();
        return  $this->sendResponse($campaigns, 'Campaigns retrieved succesfully');
    }

    public function getWeeklyCampaignsData()
    {

        $taboolaService = new TaboolaApi();

        $today = date('Y-m-d');

        try {

            $campaignData = $taboolaService->get(
                '/api/1.0/bidberrysrl-sy1-localservices-sc/reports/campaign-summary/dimensions/week?start_date=2022-03-01&end_date=' . $today,
            );

            $result = [
                'clicks' => [],
                'spent' => [],
                'dates' => []
            ];

            foreach ($campaignData['results'] as $entry) {
                $result['clicks'][] = $entry['clicks'];
                $result['spent'][] = $entry['spent'];
                $result['dates'][] = date('m-d-y', strtotime($entry['date']));
            }

            return  $this->sendResponse($result, 'Campaigns data retrieved succesfully');
        } catch (RequestException $e) {
            return $this->sendError('External API error.');
        }
    }

    public function getCampaignsData()
    {
        $taboolaService = new TaboolaApi();

        $today = date('Y-m-d');

        try {

            $campaignData = $taboolaService->get(
                '/api/1.0/bidberrysrl-sy1-localservices-sc/reports/campaign-summary/dimensions/month?start_date=2022-03-01&end_date=' . $today,
            );

            $result = $this->extractData($campaignData['results']);

            return  $this->sendResponse($result, 'Campaigns data retrieved succesfully');
        } catch (RequestException $e) {
            return $this->sendError('External API error.');
        }
    }


    /**
     * Get data request to update the domain.
     *
     * @param Request $request The request.
     * @param int $id The domain id.
     */
    public function update(Request $request, $id)
    {

        // return $this->sendResponse(new DomainResource($domain), 'Domain updated successfully.');
    }

    function extractData($data)
    {
        $clicks = [];
        $spends = [];
        $dates = [];
        $totalSpent = 0;
        $totalConversions = 0;
        $totalClicks = 0;
        $totalImpressions = 0;
        $totalViewableImpressions = 0;
        $avgCPC = 0;
        $vCTR = 0;

        foreach ($data as $item) {
            $clicks[] = $item['clicks'];
            $spends[] = $item['spent'];
            $date = date_create($item['date']);
            $formattedDate = date_format($date, 'M Y');
            $dates[] = $formattedDate;

            $totalSpent += $item['spent'];
            $totalConversions += $item['cpa_actions_num'];
            $totalClicks += $item['clicks'];
            $totalViewableImpressions += $item['visible_impressions'];
        }

        $avgCPC = $totalSpent / $totalClicks;
        $vCTR = ($totalClicks / $totalViewableImpressions) * 100;

        $result = [
            'clicks' => $clicks,
            'spends' => $spends,
            'dates' => $dates,
            'totalSpent' => $totalSpent,
            'totalConversions' => $totalConversions,
            'totalClicks' => $totalClicks,
            'totalImpressions' => $totalViewableImpressions,
            'avgCPC' => $avgCPC,
            'vCTR' => $vCTR
        ];

        return $result;
    }




    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $taboolaService = new TaboolaApi();

        try {

            $taboolaService->delete(
                '/api/1.0/bidberrysrl-sy1-localservices-sc/campaigns/' .  $id
            );

            return $this->sendResponse([], 'Campaign deleted successfully.');
        } catch (RequestException $e) {
            return $this->sendError('External API error.');
        }
    }
}
