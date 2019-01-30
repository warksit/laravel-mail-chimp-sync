<?php namespace Warksit\LaravelMailChimpSync\Jobs;

use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Warksit\LaravelMailChimpSync\MailChimp\MailChimpAuth;
use Warksit\LaravelMailChimpSync\Events\AddInterestFailed;
use Warksit\LaravelMailChimpSync\MailChimp\InterestActions;
use Warksit\LaravelMailChimpSync\Exceptions\MailChimpTooManyInterestsException;

class AddInterestGroup implements ShouldQueue
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
        try {
            $mailChimp->add($this->model);
        } catch (MailChimpTooManyInterestsException $e) {
            event(new AddInterestFailed($this->model));
        }
    }
}
