<?php namespace Warksit\LaravelMailChimpSync\Jobs;

use App\Jobs\Job;
use Illuminate\Database\Eloquent\Model;
use Warksit\LaravelMailChimpSync\MailChimp\SubscriptionActions;

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
     * Create a new job instance.
     *
     * @param Model $model
     */
    public function __construct(Model $model, $emailToDelete)
    {
        $this->model = $model;
        $this->emailToDelete = $emailToDelete;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(SubscriptionActions $mailchimp)
    {
        \Log::info('Handling: ' . get_class($this));
        if( ! $this->model->mailingList)
            return;

        $this->model->mailingList->delete();
        
        $mailchimp->unSubscribe($this->model, $this->emailToDelete);
        
        \Log::info('UnSubscribe ' . $this->emailToDelete . ' from ' . $this->model->getMailingListId());
    }
}
