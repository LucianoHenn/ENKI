<?php

namespace App\Services\Booster\RevenuePartners;

use Log;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Booster\RelatedKeyword;
use App\Models\Booster\Keyword;
use App\Models\Booster\KeywordStats;
use Illuminate\Support\Facades\DB;
use Carbon\CarbonPeriod;



class YadsRevenuePartnerService extends BaseRevenuePartnerService
{

    protected $service = 'YadsRevenuePartnerService';
    protected static $athena_table_name = 'vw_booster_yads';
}