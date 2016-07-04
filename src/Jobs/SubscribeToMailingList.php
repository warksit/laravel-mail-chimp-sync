<?php namespace Warksit\LaravelMailChimpSync\Jobs;

use App\Company\SetActiveCompany;
use App\Jobs\Job;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Warksit\LaravelMailChimpSync\Interfaces\MailingListModel;
use Warksit\LaravelMailChimpSync\MailChimp\SubscriptionActions;

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
        if (!$model instanceof MailingListModel)
            throw new \InvalidArgumentException('Model does not implement MailingListModel');
        
        $this->model = $model;
    }

    /**
     * Execute the job.
     *
     * @param SubscriptionActions $mailChimp
     */
    public function handle(SubscriptionActions $mailChimp)
    {
        \Log::info('Handling: ' . get_class($this));

        SetActiveCompany::to($this->model->company);

        if ($this->model->getMailingListOptedOut())
            return;
        
        $mailChimp->subscribe($this->model);
    }
}
