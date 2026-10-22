<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;

// Several of the package's jobs extend the host application's App\Jobs\Job, which
// every Laravel app had when this package was written. This stands in for it.
abstract class Job
{
    use Queueable;
}
