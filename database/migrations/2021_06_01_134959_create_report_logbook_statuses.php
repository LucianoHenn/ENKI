<?php

use App\Models\ARC\ReportLogbook;
use App\Models\ARC\ReportLogbookStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportLogbookStatuses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('report_logbook_statuses', function (Blueprint $table) {
            $table->integer('id');
            $table->string('name');
            $table->timestamps();
            $table->primary('id');
        });
        $this->generate_status();

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('report_logbook_statuses');
    }

    private function generate_status()
    {
        $status = [
            ["id" => ReportLogbookStatus::STATUS_REQUESTED, "name" => "REQUESTED"],
            ["id" => ReportLogbookStatus::STATUS_DOWNLOADING, "name" => "DOWNLOADING"],
            ["id" => ReportLogbookStatus::STATUS_DOWNLOADED, "name" => "DOWNLOADED"],
            ["id" => ReportLogbookStatus::STATUS_IMPORTING, "name" => "IMPORTING"],
            ["id" => ReportLogbookStatus::STATUS_IMPORTED, "name" => "IMPORTED"],
            ["id" => ReportLogbookStatus::STATUS_NOTIFYING, "name" => "NOTIFYING"],
            ["id" => ReportLogbookStatus::STATUS_NOTIFIED, "name" => "NOTIFIED"],
            ["id" => ReportLogbookStatus::STATUS_ERROR_REQUESTED, "name" => "ERROR_REQUESTED"],
            ["id" => ReportLogbookStatus::STATUS_ERROR_DOWNLOADED, "name" => "ERROR_DOWNLOADED"],
            ["id" => ReportLogbookStatus::STATUS_ERROR_IMPORTED, "name" => "ERROR_IMPORTED"],
            ["id" => ReportLogbookStatus::STATUS_ERROR_NOTIFIED, "name" => "ERROR_NOTIFIED"],
        ];

        foreach ($status as $st) {
            $newStatus = new ReportLogbookStatus();
            $newStatus->id = $st['id'];
            $newStatus->name = $st['name'];
            $newStatus->save();
        }
    }
}
