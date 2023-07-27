<?php

namespace App\Jobs\Jobber\Plugins\Taboola;

use App\Jobs\Jobber\BaseJobber;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use App\Services\Utils\Mustache;
use Illuminate\Support\Collection;
use App\Models\Jobber;
use App\Models\Option;


/**
 * Class CreateCampaigns
 */
class CreateCampaigns extends BaseJobber implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    public static function validate(array $args)
    {
        foreach ($args['keywords'] as $keyword) {
            if (count($keyword['images']) === 0)
                throw new InvalidArgumentException('Missing Image: "' . $keyword['keyword']  . '" must be at have least one image attached');
            foreach ($keyword['images'] as $image) {
                if ($image['width']  < 600  || $image['height'] < 400) {
                    throw new InvalidArgumentException('Invalid Image: "' . $image['image_name']  . '" must be at least of 600 x 400 px');
                }
            }
        }

        if ($args['template']['budget']['spending_limit_model'] !== "NONE") {
            if ($args['template']['budget']['spending_limit'] <= $args['template']['budget']['daily_budget']) {
                throw new InvalidArgumentException('Spending limit must be larger than daily budget');
            }
        }

        if ($args['template']['budget']['daily_budget'] < 1) {
            throw new InvalidArgumentException('Budget must be a positive number above 1');
        }
        if ($args['template']['budget']['daily_budget'] < 1) {
            throw new InvalidArgumentException('Daily budget must be a positive number above 1');
        }
        if ($args['template']['budget']['cpc'] < 0.0093) {
            throw new InvalidArgumentException('CPC Amount cannot be lower than 0.0093 EUR');
        }
        if (($args['template']['budget']['daily_budget'] / $args['template']['budget']['cpc']) < 30) {
            throw new InvalidArgumentException('Daily budget should be at least 30 times more than the CPC');
        }
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function run()
    {
        $mustache = Mustache::new(['delimiters' => '[ ]', 'escape' => fn ($value) => $value]);
        $accountId = 'bidberrysrl-sy1-localservices-sc';
        $budget = $this->args['template']['budget'];
        $targeting = $this->args['template']['targeting'];
        $campaignSettings = $this->args['template']['campaign_settings'];
        $campaigns = new Collection;

        // Here we get the platform targeting devices
        $option = Option::where('name', 'taboola_platform_targeting')->select('value')->first()['value'];
        $taboolaTargetingOptions = [];
        foreach ($option as $item) {
            $taboolaTargetingOptions[strtolower($item['value'])] = $item['name'];
        }
        $platform_targeting = array_keys(array_filter($targeting['platform_targeting'], function ($value) {
            return $value === true;
        }));
        $targeting_devices = [];
        foreach ($platform_targeting as $key) {
            $targeting_devices[] = $taboolaTargetingOptions[strtolower($key)];
        }

        $campaignTargeting['platform_targeting'] = [
            'type' => "INCLUDE",
            'value' => $targeting_devices,
            'href' => null
        ];

        // HERE WE MAP TO SANITZE THE TARGETING DATA
        // THE OS_TARGETING OBJECT IS DIFFERENT THAN THE OTHERS
        if (!empty($targeting['os_targeting'])) {
            $values = [];
            foreach ($targeting['os_targeting'] as $os) {
                array_push($values, ["os_family" => $os['value']]);
            }
            $campaignTargeting['os_targeting'] =
                [
                    'type' => $targeting['excludes']['os_targeting'] ? "EXCLUDE" : "INCLUDE",
                    'value' => $values,
                    'href' => null
                ];
        }



        unset($targeting['os_targeting']);
        unset($targeting['platform_targeting']);

        foreach ($targeting as $key => $value) {
            if (!empty($targeting[$key]) && $key !==  'excludes') {

                $campaignTargeting[$key] =
                    [
                        'type' => $targeting['excludes'][$key] ? "EXCLUDE" : "INCLUDE",
                        'value' =>  array_map(function ($item) {
                            return $item['value'];
                        }, $value),
                        'href' => null
                    ];
            }
        }


        foreach ($this->args['ad_accounts'] as $adAccount) {

            $accountId =  $adAccount['account']['value'];

            foreach ($this->args['keywords'] as $keyword) {

                $url = $this->args['uses_parking_domain'] ? $this->args['parking_domains'][$keyword['keyword']] :  $adAccount['domain']['element']['url'];

                Log::info("[" . static::class . "][" . __FUNCTION__ . "] Processing Keyword: " .  $keyword['keyword']);

                $category = $keyword['category'] && array_key_exists('name', $keyword['category'])  ? $keyword['category']['name'] : '';


                // JUST TO CHANGE PHON TO MOB
                $platform =  implode('_', $targeting_devices);
                $key = array_search("PHON", $targeting_devices);
                if ($key !== false) {
                    $array = array_replace($targeting_devices, [$key => "MOB"]);
                    $platform =  implode('_', $array);
                }

                // FOR NOW: {COUNTRYCODE}__{USER}____{KEYWORD}__{CATEGORY}__{DEVICE}__{SUFFIX}
                // EXAMPLE: es_ste_placas solares autoconsumo_home and garden_msn_mob_shp_bst

                $campaignName = strtoupper(implode('_', [
                    $keyword['country']['code'],
                    $this->args['user']['name'],
                    $keyword['keyword'],
                    $category,
                    $platform
                ]));

                if ($campaignSettings['name_suffix']) {
                    $campaignName .= '_' . strtoupper($campaignSettings['name_suffix']);
                }

                $headlines = [];
                foreach ($this->args['headlines'] as $headline) {
                    $placeholders = array("[keyword]", "[brand]");
                    $replacements = array($keyword['keyword'], $campaignSettings['brand_name']);
                    $newHeadline = str_replace($placeholders, $replacements,  $headline);
                    array_push($headlines,  $newHeadline);
                }

                $headlinePlaceholders = [
                    'query' => $keyword['keyword'],
                    'market' => $keyword['country']['code'],
                ];

                $url = $mustache->render($url, array_map('urlencode', $headlinePlaceholders));



                $campaign = [
                    'name' => $campaignName,
                    'budget' => $budget,
                    'targeting' => $campaignTargeting,
                    'campaign_settings' => $campaignSettings,
                    'keyword' => $keyword,
                    'headlines' =>   $headlines,
                    'url' =>  $url,
                    'account_id' => $accountId,
                    'cta' => $this->args['template']['cta']['value'],
                    'description' => array_key_exists('description', $this->args['template']) ? $this->args['template']['description'] : ""
                ];
                $campaigns->push($campaign);
            }
        }
        // Creating sub jobs
        Log::info("[" . static::class . "][" . __FUNCTION__ . "] Creating sub jobs...");
        $jobs = $campaigns->map(
            fn ($campaign) => Jobber::enqueue([
                'description' => "fork||{$this->jobManager->id}||loadcampaigns||{accountId}",
                'class' => 'Taboola\\LoadCampaigns',
                'args' => $campaign,
                'creator' => $this->jobManager->creator_id,
            ])
        );
        Log::info("[" . static::class . "][" . __FUNCTION__ . "] Created sub jobs: {$jobs->map(fn ($j) =>$j->id)->implode(', ')}");

        Log::info("[" . static::class . "][" . __FUNCTION__ . "] Waiting for sub jobs {$jobs->map(fn ($j) =>$j->id)->implode(', ')}...");
        Jobber::wait(...$jobs);
        Log::info("[" . static::class . "][" . __FUNCTION__ . "] Completed sub jobs {$jobs->map(fn ($j) =>$j->id)->implode(', ')}");
        $this->jobManager->updateSummary("$.jobs", $jobs->map(fn ($job) => $job->summary));
    }
}
