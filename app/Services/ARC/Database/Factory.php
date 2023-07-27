<?php

namespace App\Services\ARC\Database;

use App\Exceptions\ARC\ReportException;
use App\Models\ARC\Abstracts\BaseReportDataModel;
use App\Models\ARC\ReportLogbook;
use Illuminate\Support\Facades\Log;

class Factory
{
    private const NS_MODELS_ARC_SOURCES_PREFIX = 'App\Models\ARC\Providers';
    private const NS_MODELS_ARC_SOURCES_SUFFIX = 'ReportData';

    public static function getReportModel(ReportLogbook $request): ?BaseReportDataModel
    {
        $class = implode('\\', array_filter([
            self::NS_MODELS_ARC_SOURCES_PREFIX,
            $request->source,
            $request->source . $request->report_type . self::NS_MODELS_ARC_SOURCES_SUFFIX
        ]));

        if (!class_exists(($class))) {
            //old-format for model path
            $class = implode('\\', array_filter([
                self::NS_MODELS_ARC_SOURCES_PREFIX,
                $request->source . $request->report_type . self::NS_MODELS_ARC_SOURCES_SUFFIX
            ]));

            if (!class_exists(($class))) {
                throw new ReportException("Report Model class {$class} for source {$request->source} {$request->report_type} is missing.");
            }
        }

        $c = new $class();
        return $c->setTable($c->getReportTableName($request));
    }
}
