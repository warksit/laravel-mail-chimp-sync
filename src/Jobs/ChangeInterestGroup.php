<?php namespace Warksit\LaravelMailChimpSync\Jobs;

use App\Jobs\Job;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Warksit\LaravelMailChimpSync\MailChimp\InterestActions;

class ChangeInterestGroup extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;
    /**
     * @var Model
     */
    private $model;

    /**
     * Create a new job instance.
     *
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(InterestActions $mailChimp)
    {
        $mailChimp->updateName($this->model);
    }
}
