<?php

return [

    // dispatches jobs at creation time (see App\Models\Job::enqueue() and Kernel.php)

    'dispatch' => (bool) env('JOBS_DISPATCH_AT_CREATION', false),

    // scripts default Creator ID (see App\Models\Job::enqueue())

    'creator'  => (int)  env('JOBS_DEFAULT_CREATOR_ID'),

    // using App\Models\Job::wait() , polling time

    'waitPollingTime' => 10,

];

