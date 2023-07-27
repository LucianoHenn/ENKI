<?php

namespace App\Http\Controllers\API;


use App\Models\Jobber;
use App\Models\User;
use Auth;
use DB;
use Illuminate\Http\Request;
use Exception;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Jobs\Jobber\Plugins\Facebook\CreateCampaigns;

class JobberController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $job_id = $request->id ?? $request->filters_id ?? $request->filters['id'] ?? null;
        $job_class = $request->class ?? $request->filters_class ?? $request->filters['class'] ?? null;
        $job_creator = $request->creator ?? $request->filters_creator ?? $request->filters['creator'] ?? null;
        $job_description = $request->description ?? $request->filters_description ?? $request->filters['description'] ?? null;
        $sort_field = $request->sortField ?? $request->sort_field ?? $request->sort['field'] ?? 'id';
        $sort_type = $request->sortType ?? $request->sort_type ?? $request->sort["type"] ?? 'desc';
        $perPage = $request->get('perPage', 10);


        $query = Jobber::query()
            ->with('creator');

        if (!empty($job_id)) {
            if ($exploded = explode(',', $job_id)) {
                $query->whereIn('id', $exploded);
            } else {
                $query->where('id', $job_id);
            }
        }

        if (!empty($job_class)) {
            $query->where('class', 'LIKE', "%" . str_replace('\\', '\\\\', $job_class) . "%");
        }
        if (!empty($job_creator)) {
            $query->whereHas('creator', function ($qr) use ($job_creator) {
                $qr->where('username', 'like', $job_creator . '%');
            });
        }
        if (!empty($job_description)) {
            $query->where('description', 'LIKE', "%{$job_description}%");
        }

        $jobs = $query
            ->with('creator')
            ->orderByRaw(([
                'class'       => 'class',
                'description' => 'description',
            ][$sort_field] ?? 'id') . ' ' . ($sort_type ?? 'desc'))
            ->paginate($perPage);

        $items = collect($jobs->items())->map(fn ($job) => $job->toArray() + [
            'status' => match (true) {
                $job->isFailed() => "failed",
                $job->isFinished()   => 'done',
                $job->isRunning()       => 'running',

                default              => 'pending',
            },
            'step'   => match (true) {
                $job->isQueued()     => 'queued',
                $job->isDispatched() => 'dispatched',
                $job->isRunning()    => 'running',
                $job->isFinished()   => 'finished',
                default              => throw new Exception('Unknown step'),
            },
            'completed' => $job->isCompleted(),
            'failed'    => $job->isFailed(),
            'file'      => $job->getFileTemporaryUrlAttribute(),
        ]);
        $jobs = $jobs->toArray();
        $jobs['data'] = $items;

        return $jobs;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreJobRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $job = $request->toArray();

        if (isset($job['args']) && is_string($job['args'])) {
            $job['args'] = json_decode($job['args'], true);
        } elseif (!isset($job['args'])) {
            $job['args'] = [];
        }

        $jobber =  Jobber::enqueue($job);

        return $this->sendResponse($jobber->toArray(), 'Jobber created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Jobber  $job
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return Jobber::findOrFail($id);
    }


    public  function getSubJobNumbers($string)
    {
        $pattern = '/Waiting for sub jobs (\d+(?:, \d+)*)/';
        $matches = [];

        if (preg_match($pattern, $string, $matches)) {
            if (!empty($matches[1])) {
                $numbers = explode(', ', $matches[1]);
                return $numbers;
            } else {
                return [$matches[1]];
            }
        }
    }

    /**
     * Get the logs for each Jobber
     *
     * @return \Illuminate\Http\Response
     */
    public function showLogs(Request $request, $id)
    {

        $jobber = Jobber::find($id);

        if (!$jobber)
            return $this->sendError('The logs of Jobber #' . $id . ' are not available');


        $jobsIds = [intval($id)];

        // To get the subjobs of the create campaign
        foreach ($jobber->log as $log) {
            $string = '[run] Waiting for sub jobs';
            if (strpos($log['message'], $string) !== false) {
                array_push($jobsIds, $this->getSubJobNumbers($log['message']));
            }
        }

        $flattenedArray = [];

        array_walk_recursive($jobsIds, function ($value) use (&$flattenedArray) {
            $flattenedArray[] = $value;
        });

        $uniqueArray = array_unique($flattenedArray);



        return $this->sendResponse(['logs' => $jobber->log, 'status' =>  match (true) {
            ($jobber->isFailed()) => "failed",
            $jobber->isFinished()   => 'done',
            $jobber->isRunning()       => 'running',

            default              => 'pending',
        }, 'className' => $jobber->class, 'jobsIds' =>  $uniqueArray], 'Logs retrieved succesfully');
    }
}
