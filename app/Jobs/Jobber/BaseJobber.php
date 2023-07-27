<?php

namespace App\Jobs\Jobber;

use App\Models\Jobber;
use BadMethodCallException;
use Closure;
use Log;
use LogicException;
use Throwable;
use InvalidArgumentException;

abstract class BaseJobber
{
    protected Jobber $jobManager;
    protected ?array $args = null;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        Jobber $jobManager,
        $args,
    ) {
        $this->jobManager = $jobManager;

        $this->jobManager->resetLog();

        $this->args = $args;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function __call($method, $args)
    {
        if (!in_array($method, ['enqueue', 'run'])) {
            throw new BadMethodCallException();
        }
        // abstract public function run();
        return $this->{$method}($args);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $this->jobManager->captureLog(function () {

            $this->jobManager->run();

            if ($this->implementsEnqueue())
                $this->enqueue();

            $this->run();

            $this->jobManager->finish();
        });
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $e): void
    {

        $this->jobManager->finish([
            'message'  => $e->getMessage(),
            'code'     => $e->getCode(),
            'file'     => $e->getFile(),
            'line'     => $e->getLine(),
            'trace'    => $e->getTrace(),
            'previous' => $e->getPrevious(),
        ]);

        if ($this->jobManager->isRetryable()) {
            $this->jobManager->redispatch();
        }
    }

    protected function implementsEnqueue(): bool
    {
        return method_exists(static::class, 'enqueue');
    }


    public static function validate(array $args)
    {
    }
}
