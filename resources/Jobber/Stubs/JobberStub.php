<?php

namespace App\Jobs\Jobber\Plugins\__JOBBERNAMESPACE__;

use App\Jobs\Jobber\BaseJobber;

use App\Services\Utils\StorageObject;
use App\Services\Utils\Mustache;
use App\Services\Utils\App;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class __JOBBERNAME__
 */
class __JOBBERNAME__ extends BaseJobber implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;



    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function enqueue(
        array $args = null
    ) {
        //here you can assign parameters or validate them

    }

    protected string $myParam;
    //define other properties


    /**
     * Execute the job.
     *
     * @return void
     */
    public function run()
    {
        $this->jobManager->updateSummary("$", ['report' => '']);
        $jobberFilePath = '{{ env }}/Jobbers/__JOBBERNAMESPACE__/__JOBBERNAME__/{{ date }}-{{ id }}.xlsx';
        $report = (new Mustache)->render($jobberFilePath, [
            'env'  => App::environment(),
            'date' => $this->jobManager->created_at->toISOString(),
            'id'   => $this->jobManager->id,
        ]);

        $content = json_encode([$this->myParam]); //just an example
        //$content = //DOSOMETHING;
        StorageObject::new($report)->put($content);


        $this->jobManager->updateSummary("$.report", $report);
    }
}
