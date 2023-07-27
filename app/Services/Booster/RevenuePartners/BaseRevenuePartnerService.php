<?php

namespace App\Services\Booster\RevenuePartners;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Collection;

use App\Models\Booster\Keyword;

use Illuminate\Support\Facades\DB;


use Exception;

use App\Services\Booster\Utils\AthenaClient;

class BaseRevenuePartnerService
{
    protected $relatedKeywords;
    protected $keyword;
    protected $keywordsStats;
    protected static $table_name = 'booster_keyword_stats';
    protected static $athena_table_name = '';
    protected $service = 'BaseRevenuePartnerService';
    
    public function __construct(Keyword $keyword, Collection $relatedKeywords)
    {
        $this->relatedKeywords  = $relatedKeywords;
        $this->keyword          = $keyword;
    }


    protected function getMaxPrefix()
    {
        return DB::table(static::$table_name)
        ->where('revenue_partner', $this->keyword->revenue_partner)
        ->max('order_prefix');
    }

    protected function getKeywordRecords($kwds, $date)
    {
        DB::beginTransaction();

        try {
            $max_prefix = $this->getMaxPrefix();
        
            $data = DB::table(static::$table_name)
            ->selectRaw('LOWER(keyword) as keyword,
            LOWER(input_keyword) as input_keyword,
            SUM(impressions) as impressions,
            SUM(clicks) as clicks,
            ROUND((SUM(gross_revenue)/SUM(impressions)*1000),2) as erank,
            SUM(gross_revenue) as gross_revenue')
            ->where('order_prefix', $max_prefix)
            ->where('revenue_partner', $this->keyword->revenue_partner)
            ->where("market", $this->keyword->market_code)
            ->where('device', $this->keyword->device)
            ->where('input_keyword', $this->keyword->keyword)
            ->whereIn('keyword', $kwds)
            ->where('stats_timestamp', '>=', $date->format('Y-m-d H:00:00'))
            ->groupBy('keyword')
            ->groupBy('input_keyword')
            ->get();

            DB::commit();

            return $data;
        }
        catch(\Exception $e) {
            Log::error('[RevenuePartnerService]['.$this->keyword->revenue_partner.'] DB Error: ' . $e->getMessage());
            DB::rollBack();
            return [];
        }
    }

    public static function getKeywordsStats()
    {
        $athena_client = new AthenaClient(
            config('services.enki-report-athena.version'),
            config('services.enki-report-athena.region'),
            config('services.enki-report-athena.key'),
            config('services.enki-report-athena.secret')
        );

        
        $athena_client->setOutputLocation(config('services.enki-report-athena.output_location'));
        $athena_client->setDb(config('services.enki-report-athena.db_name'));

        $query = 'SELECT * FROM ' . static::$athena_table_name;

        return $athena_client->getData($query);
    }



    public function retrieveEconomicalValues()
    {
        if(empty(static::$table_name)) return false;
        
        Log::info("[{$this->service}][retrieveEconomicalValues]: " . json_encode([
            'input_keyword' => $this->keyword->keyword,
            'identifier'    => $this->keyword->identifier,
            'device'        => $this->keyword->device,
            'market_code'   => $this->keyword->market_code,
            'source'        => $this->keyword->source,
            'keywords'      => $this->relatedKeywords->all()
        ]));
        // we know that these keywords are already roi_id isolated
        $kwds = $this->relatedKeywords->pluck("keyword");

        $date = Carbon::today()->subDays(3);


        $keyword_records = $this->getKeywordRecords($kwds, $date);

        Log::info("[{$this->service}]Query response:" . json_encode( [$keyword_records]));
        $this->keywordsStats =  $keyword_records;
    }

    public function getKwdMetrics(string $keyword): array
    {
        $res = [
            "erank"             => 0.0,
            "impressions"       => 0,
            "clicks"            => 0,
            "gross_revenue"     => 0.0
        ];

        $kwd = $this->keywordsStats->firstWhere('keyword', strtolower($keyword));

        if ($kwd) {
            //Log::debug("Match!", [$kwd]);
            $res = [
                "erank"             => (float)  $kwd->erank,
                "impressions"       => (int)    $kwd->impressions,
                "clicks"            => (int)    $kwd->clicks,
                "gross_revenue"     => (float)  $kwd->gross_revenue
            ];
        }

        return $res;
    }
}