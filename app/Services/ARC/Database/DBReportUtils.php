<?php

namespace App\Services\ARC\Database;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

class DBReportUtils
{
    public static function addBaseFields(Blueprint $table)
    {
        $table->date('date');
        $table->string('identifier');
    }

    public static function addEconomicFields(Blueprint $table)
    {
        $table->decimal('revenue_share', 6, 5);
        $table->char('currency', 3);
        $table->decimal('amount', 14, 4);
        $table->decimal('amount_eur', 14, 4);
        $table->decimal('amount_usd', 14, 4);
    }


    public static function addTimestampsAndBaseIndex(Blueprint $table)
    {
        $table->timestamps();
        $table->index(['date', 'identifier'], 'idx_date_identifier');
        $table->index(['identifier'], 'idx_identifier');

    }
}
