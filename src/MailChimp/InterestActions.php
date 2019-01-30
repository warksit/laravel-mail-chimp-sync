<?php namespace Warksit\LaravelMailChimpSync\MailChimp;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Contracts\Config\Repository;
use Warksit\LaravelMailChimpSync\Exceptions\MailChimpException;
use Warksit\LaravelMailChimpSync\Interfaces\CanSyncMailChimpInterest;
use Warksit\LaravelMailChimpSync\Models\Interest;

class InterestActions extends MailChimpActions
{
    /**
     * @var Interest
     */
    private $interest;

    /**
     * SubscriptionActions constructor.
     */
    public function __construct(Client $guzzle, Interest $interest)
    {
        parent::__construct($guzzle);
        $this->interest = $interest;
    }

    public function add($model)
    {
        $this->checkModelImplementsInterface($model);
        $this->setAuth($model->getMailChimpAuth());

        if($model->interest)
        {
            $response = $this->process(
                'PATCH',
                $this->generateUri($model) . "/{$model->interest->interest_id}",
                [
                    'json' => [
                        'name' => $model->getInterestName(),
                        'display_order' => $model->getInterestDisplayOrder(),
                    ],
                ]
            );
            $model->interest->touch();
        } else {
            $response = $this->process(
                'POST',
                $this->generateUri($model),
                [
                    'json' => [
                        'name' => $model->getInterestName(),
                        'display_order' => $model->getInterestDisplayOrder(),
                    ],
                ]
            );
            $id = json_decode($response->getBody())->id;
            $this->interest->interest_id = $id;
            $model->interest()->save($this->interest);
        }
    }

    public function remove($model)
    {
        $this->checkModelImplementsInterface($model);
        $this->setAuth($model->getMailChimpAuth());
        if ($model->interest) {
            $this->process(
                'DELETE',
                $this->generateUri($model) . "/{$model->interest->interest_id}"
            );
            $model->interest->delete();
        }
    }
    
    /**
     * @param $model
     * @return string
     */
    protected function generateUri($model)
    {
        return "/3.0/lists/{$model->getInterestListId()}/interest-categories/{$model->getInterestCategoryId()}/interests";
    }

    private function checkModelImplementsInterface($model)
    {
        if (!$model instanceof CanSyncMailChimpInterest)
            throw new \InvalidArgumentException('model needs to implement ' . CanSyncMailChimpInterest::class);
    }

}