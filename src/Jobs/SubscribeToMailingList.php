<?php namespace Warksit\LaravelMailChimpSync\Jobs;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Warksit\LaravelMailChimpSync\MailChimp\MailChimpAuth;
use Warksit\LaravelMailChimpSync\MailChimp\SubscriptionActions;
use Warksit\LaravelMailChimpSync\Interfaces\CanSyncMailChimpMember;

class SubscribeToMailingList extends Job implements ShouldQueue
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
    public function __construct($model)
    {
        if (!$model instanceof CanSyncMailChimpMember)
            throw new \InvalidArgumentException('Model does not implement ' . CanSyncMailChimpMember::class);
        
        $this->model = $model;
    }

    /**
     * Execute the job.
     *
     * @param SubscriptionActions $mailChimp
     */
    public function handle(SubscriptionActions $mailChimp)
    {
        if ($this->model->getMailingListOptedOut())
            return;

        $mailChimp->subscribe($this->model);
    }
}
