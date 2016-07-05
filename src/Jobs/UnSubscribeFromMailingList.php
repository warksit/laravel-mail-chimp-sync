<?php namespace Warksit\LaravelMailChimpSync\Jobs;

use App\Jobs\Job;
use Illuminate\Database\Eloquent\Model;
use Warksit\LaravelMailChimpSync\MailChimp\SubscriptionActions;
use Warksit\LaravelMailChimpSync\Interfaces\CanSyncMailChimpMember;

class UnSubscribeFromMailingList extends Job
{
    /**
     * @var Model
     */
    private $model;
    /**
     * @var
     */
    private $emailToDelete;
    /**
     * @var MailChimpAuth
     */
    private $auth;

    /**
     * Create a new job instance.
     *
     * @param Model $model
     */
    public function __construct(Model $model, $emailToDelete)
    {
        if (!$model instanceof CanSyncMailChimpMember)
            throw new \InvalidArgumentException('Model does not implement ' . CanSyncMailChimpMember::class);

        $this->model = $model;
        $this->emailToDelete = $emailToDelete;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(SubscriptionActions $mailChimp)
    {
        if( ! $this->model->mailingList)
            return;

        $this->model->mailingList->delete();

        $mailChimp->unSubscribe($this->model, $this->emailToDelete);
    }
}
