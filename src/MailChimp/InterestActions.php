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
            try {
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
            } catch (MailChimpException $e) {
                $id = $this->unclaimedInterestNamed($model, $e);
            }
            $this->interest->interest_id = $id;
            $model->interest()->save($this->interest);
        }
    }

    /**
     * MailChimp refuses a second interest with the same name, so a model whose
     * interest row has gone missing can never be synced again by POSTing. Adopt
     * the existing interest instead - but only if no other model holds it, or
     * deleting either model would delete the interest from under the other.
     *
     * @param $model
     * @param MailChimpException $e
     * @return string
     * @throws MailChimpException
     */
    private function unclaimedInterestNamed($model, MailChimpException $e)
    {
        if (strpos($e->getMessage(), 'already exists on the list') === false) {
            throw $e;
        }

        $response = $this->process('GET', $this->generateUri($model) . '?count=1000');

        foreach (json_decode($response->getBody())->interests as $interest) {
            if (strcasecmp(trim($interest->name), trim($model->getInterestName())) === 0
                && ! Interest::where('interest_id', $interest->id)->exists()) {
                return $interest->id;
            }
        }

        throw $e;
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