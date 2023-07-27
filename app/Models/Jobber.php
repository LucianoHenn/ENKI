<?php

namespace App\Models;

use App\Models\User;
use App\Services\Utils\App;
use App\Services\Utils\Mustache;
use App\Services\Utils\StorageObject;
use Auth;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Log;
use Illuminate\Support\Facades\Storage;

class Jobber extends Model
{
    use HasFactory;



    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'description',
        'class',
        'args',
        'creator_id',
        'summary',
        'error',
        'log',
        'is_retryable',
        'attempts'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'args'          => 'array',
        'dispatched_at' => 'datetime',
        'run_at'        => 'datetime',
        'finished_at'   => 'datetime',
        'summary'       => 'array',
        'error'         => 'array',
        'log'           => 'array',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'args' => '[]',
        'log'  => '[]',
        'summary' => '{}',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }


    public static function enqueue(array $jobData)
    {

        $job_class = 'App\\Jobs\\Jobber\\Plugins\\' . $jobData['class'];

        $job_class::validate($jobData['args'] ?? []);

        $job = self::create([
            'class'       => $jobData['class'],
            'args'        => $jobData['args'] ?? [],
            'description' => $jobData['description'] ?? 'Job of ' . Carbon::now()->format('Y-m-d'),
            'creator_id'  => $jobData['creator'] ?? Auth::id() ?? config('jobber.creator'),
        ]);

        if (array_key_exists('file', $jobData)) {
            $job->storeFileInS3($jobData['file']->get(), $jobData['file']->getClientOriginalName());
        }

        if (!config('jobber.dispatch')) {
            // Dispatch skipped ✓
            return $job;
        }

        $job->dispatch();
        $queue = static::getQueue($job->class);

        $job_class::dispatch($job, $job->args)->onQueue($queue);

        return $job;
    }

    public static function wait(self ...$jobs)
    {
        $pollingTime = config('jobber.waitPollingTime');

        while (collect($jobs)->contains(fn ($j) => !$j->refresh()->isFinished())) {

            sleep($pollingTime);
        }
    }

    public static function getQueue(string $class): string
    {
        foreach (config('horizon.defaults') as $supervisor => $settings) {

            if (strpos($supervisor, 'jobbers') !== FALSE) {
                foreach ($settings['queue'] as $q) {
                    if (stripos($q, $class)) {
                        return $q;
                    }
                }
            }
        }
        return 'jobbers';
    }

    // Loose checks

    public function wasDispatched(): bool
    {
        return $this->dispatched_at !== null;
    }

    public function wasRunning(): bool
    {
        return $this->run_at !== null;
    }

    // Strict checks

    public function isQueued(): bool
    {
        return !$this->wasDispatched();
    }

    public function isDispatched(): bool
    {
        return $this->wasDispatched() && !$this->wasRunning();
    }

    public function isRunning(): bool
    {
        return $this->wasRunning() && !$this->isFinished();
    }

    public function isFinished(): bool
    {
        return $this->finished_at !== null;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function isRetryable(): bool
    {
        $isRetryable = ($this->is_retryable && $this->attempts < 3 && config('queue.default') !== 'sync');

        return $isRetryable;
    }

    // Actions

    public function markAsRetryable(): void
    {
        Log::info("[" . static::class . "][" . __FUNCTION__ . "] Marking job {$this->class} #{$this->id} as retryable");
        $this->attributes['is_retryable'] = true;
        $this->attributes['attempts'] =  $this->attributes['attempts'] + 1;

        $this->save();
    }

    public function redispatch(): void
    {

        $this->resetLog();

        $this->redo();

        $job_class = 'App\\Jobs\\Jobber\\Plugins\\' . $this->class;

        $this->dispatch();

        $queue = static::getQueue($job_class);

        $job_class::dispatch($this, $this->args)->onQueue($queue)->delay(now()->addMinutes(10));
    }

    public function redo(): void
    {
        Log::info("[" . static::class . "][" . __FUNCTION__ . "] Redoing job {$this->class} #{$this->id}");
        Log::debug("[" . static::class . "][" . __FUNCTION__ . "] Job data: {$this->toJson()}");

        $this->attributes['dispatched_at'] = null;
        $this->attributes['run_at'] = null;
        $this->attributes['finished_at'] = null;

        $this->attributes['error'] = null;
        $this->attributes['summary'] = '{}';
        $this->attributes['log'] = '[]';

        $this->save();
    }

    public function dispatch(): void
    {
        if (!$this->isQueued()) {
            throw new Exception('Invalid status change');
        }
        $this->attributes['dispatched_at'] = now();
        $this->save();
        Log::info("[" . static::class . "][" . __FUNCTION__ . "] Dispatching job {$this->class} #{$this->id}");
        Log::debug("[" . static::class . "][" . __FUNCTION__ . "] Job data: {$this->toJson()}");
    }

    public function run(): void
    {
        if (!$this->isDispatched()) {
            throw new Exception('Invalid status change');
        }
        $this->attributes['run_at'] = now();
        $this->save();
        Log::info("[" . static::class . "][" . __FUNCTION__ . "] Running job {$this->class} #{$this->id}");
        Log::debug("[" . static::class . "][" . __FUNCTION__ . "] Job data: {$this->toJson()}");
    }

    public function finish(array $error = null): void
    {
        if (!$this->isRunning()) {
            throw new Exception('Invalid status change');
        }
        $this->attributes['finished_at'] = now();
        $this->error = $error;
        $this->save();
        if (!empty($error)) {
            Log::info("[" . static::class . "][" . __FUNCTION__ . "] Finishing job {$this->class} #{$this->id} with error:" . json_encode($error));
            Log::debug("[" . static::class . "][" . __FUNCTION__ . "] Job data: {$this->toJson()}");
        } else {
            Log::info("[" . static::class . "][" . __FUNCTION__ . "] Finishing job {$this->class} #{$this->id}");
            Log::debug("[" . static::class . "][" . __FUNCTION__ . "] Job data: {$this->toJson()}");
        }
    }

    // Logical checks

    public function isFailed(): bool
    {
        return $this->isFinished() && $this->attributes['error'] !== null;
    }

    public function isCompleted(): bool
    {
        return $this->isFinished() && $this->attributes['error'] === null;
    }

    public function resetLog(): void
    {
        $this->log = [];
    }

    public function captureLog($code): void
    {
        // Monolog processor
        $logProcessor = function ($record) {
            DB::statement("UPDATE {$this->getTable()} SET log = JSON_ARRAY_APPEND(log, '$', JSON_EXTRACT(?, '$')) WHERE id = ?", [json_encode($record), $this->id]);
            $this->refresh();
            return $record;
        };

        Log::pushProcessor($logProcessor);

        $code();

        Log::popProcessor()
            == $logProcessor
            || throw new LogicException("Mismatching Log processors");
    }

    public function updateSummary(string $path, $data)
    {
        DB::statement("UPDATE {$this->getTable()} SET summary = JSON_SET(summary, ?, JSON_EXTRACT(?, '$')) WHERE id = ?", [$path, json_encode($data), $this->id]);
        $this->refresh();
    }

    public function storeFileInS3($fileContent, $fileName)
    {
        $path = App::environment() . '/jobber/' .  $this->id . '/' . $fileName;
        Storage::disk('s3')->put('/' . $path, $fileContent);
        $args = json_decode($this->attributes['args'], true);

        $args['file']['url'] = $path;

        $this->attributes['args'] = $args;
        $this->update(['args' => $args]);
    }


    public function getFileTemporaryUrlAttribute()
    {
        $args = json_decode($this->attributes['args'], true);
        if (!array_key_exists('file', $args) || !$args['file']['url'])
            return null;
        return Storage::disk('s3')->temporaryUrl($args['file']['url'], now()->addMinutes(10));
    }
}
