<?php

namespace App\Jobs\Jobber\Plugins\Taboola;

use App\Jobs\Jobber\BaseJobber;
use App\Models\Country;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\RequestException;
use App\Services\Utils\Mustache;
use InvalidArgumentException;
use App\Services\APIs\TaboolaApi;
use App\Models\Image;




/**
 * Class LoadCampaigns
 */
class LoadCampaigns extends BaseJobber implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function run()
    {
        Log::info("[" . static::class . "][" . __FUNCTION__ . "] Processing Taboola Load Campaign Generator");

        $mustache = Mustache::new(['delimiters' => '[ ]', 'escape' => fn ($value) => $value]);
        $taboolaService = new TaboolaApi();
        $accountId = $this->args['account_id'];
        $url = $this->args['url'];
        $budget = $this->args['budget'];
        $campaignSettings = $this->args['campaign_settings'];
        $keyword = $this->args['keyword'];
        $headlines = $this->args['headlines'];
        $targets = $this->args['targeting'];

        try {

            $newCampaign =  $taboolaService->post(
                '/api/1.0/' . $accountId . '/campaigns/',
                array_merge(
                    array(
                        // ONLY FOR TESTING TO CREATE IT ON PAUSE
                        "is_active" => false,
                        "name" => $this->args['name'],
                        "branding_text" => $campaignSettings['brand_name'],
                        "cpc" => $budget['cpc'],
                        "spending_limit" =>  $budget['spending_limit_model'] == "NONE" ? null : $budget['spending_limit'],
                        "spending_limit_model" => $budget['spending_limit_model'],
                        "marketing_objective" => $campaignSettings['marketing_objective'],
                        "bid_type" => "OPTIMIZED_CONVERSIONS",
                        "daily_cap" =>  $budget['daily_budget'],
                        "daily_ad_delivery_model" => "STRICT",
                        "conversion_rules" =>  [
                            "rules" => [
                                $campaignSettings['conversion_event']
                            ]
                        ],

                    ),
                    $targets
                )
            );
        } catch (RequestException $e) {
            Log::warning("[" . static::class . "][" . __FUNCTION__ . "] Campaign  not created");
            return;
        }

        Log::info("[" . static::class . "][" . __FUNCTION__ . "] Taboola Campaign Created");
        $newCampaign = json_decode($newCampaign->getBody());
        $campaignId = $newCampaign->id;

        foreach ($keyword['images'] as $image) {

            $urlPlaceholders = [
                'query' => $keyword['keyword'],
                'market' => Country::find($keyword['country_id'])->code,
            ];

            $url = $mustache->render($url, array_map('urlencode', $urlPlaceholders));

            foreach ($headlines as $headline) {

                try {

                    // Capaign Item can be created with only 1 param and then be updated
                    $newCampaignItem =  $taboolaService->post(
                        '/api/1.0/' . $accountId . '/campaigns/' .  $campaignId . '/items',
                        array(
                            "url" =>  $url
                        )
                    );
                } catch (RequestException $e) {
                    Log::warning("[" . static::class . "][" . __FUNCTION__ . "] Item  not created");
                    return;
                }

                Log::info("[" . static::class . "][" . __FUNCTION__ . "] Taboola Item Created");
                $newCampaignItem = json_decode($newCampaignItem->getBody());
                $itemId =  $newCampaignItem->id;

                $retryCount = 0;
                do {
                    $campaign =  $taboolaService->get(
                        '/api/1.0/' . $accountId . '/campaigns/' .  $campaignId . '/items/' . $itemId
                    );
                    // If status is crawling, wait for some time before retrying
                    $status = $campaign['status'];
                    if ($status == "crawling") {
                        sleep(10);
                        $retryCount++;
                    }
                } while (($status === 'CRAWLING' || $status === 'CRAWLING_ERROR') && $retryCount < 10);

                try {
                    $taboolaService->post(
                        '/api/1.0/' . $accountId . '/campaigns/' .  $campaignId . '/items/' . $itemId,
                        array(
                            "title" => $headline,
                            // MINIMUN:  600 x 400
                            'thumbnail_url' => Image::getTemporaryUrl($image['path'], '6 days'),
                            //'is_active' => false
                            'cta' => ['cta_type' => $this->args['cta'] == 'None' ? 'NONE' : $this->args['cta']],
                            'description' => $this->args['description'],
                        )
                    );
                    Log::info("[" . static::class . "][" . __FUNCTION__ . "] Taboola Add  Updated");
                } catch (RequestException $e) {
                    Log::warning("[" . static::class . "][" . __FUNCTION__ . "] Item  not updated");
                }
            }
        }


        Log::info("[" . static::class . "][" . __FUNCTION__ . "] Taboola Item Updated");
        Log::info("[" . static::class . "][" . __FUNCTION__ . "] Taboola Campaign Finished");
    }
}
