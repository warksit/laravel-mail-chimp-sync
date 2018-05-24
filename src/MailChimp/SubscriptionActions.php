<?php namespace Warksit\LaravelMailChimpSync\MailChimp;

use Warksit\LaravelMailChimpSync\Objects\MailChimp;
use Warksit\LaravelMailChimpSync\Interfaces\CanSyncMailChimpMember;

class SubscriptionActions extends MailChimpActions
{
    public function subscribe($model)
    {
        $this->checkModelImplementsInterface($model);
        $this->setAuth($model->getMailChimpAuth());

        $email = $model->getMailingListEmail();

        $response = $this->process(
            'PUT',
            $this->generateUri($model, $email),
            $this->generateBody($model, $model->getMailingListInterests(), $email)
        );

        $subscriberDetails =  new MailChimp($response->getBody());
        $this->saveData($model, $subscriberDetails);

        return $subscriberDetails;
    }

    public function unSubscribe($model, $emailToDelete)
    {
        $this->checkModelImplementsInterface($model);

        $this->setAuth($model->getMailChimpAuth());

        $this->process(
            'DELETE',
             $this->generateUri($model, $emailToDelete)
        );

        return true;
    }

    private function getHash($email)
    {
        return md5(strtolower($email));
    }

    /**
     * @param $model
     * @param $email
     * @return string
     */
    private function generateUri($model, $email)
    {
        return "/3.0/lists/{$model->getMailingListId()}/members/" . $this->getHash($email);
    }

    /**
     * @param $model
     */
    private function checkModelImplementsInterface($model)
    {
        if (!$model instanceof CanSyncMailChimpMember)
            throw new \InvalidArgumentException('model needs to implement ' . CanSyncMailChimpMember::class);
    }

    /**
     * @param $model
     * @param $interests
     * @param $email
     * @return array
     * @internal param $interest
     */
    protected function generateBody($model, $interests, $email)
    {
        $data = [
            'status_if_new' => 'pending',
            'email_address' => $email,
        ];

        if(count($interests))
            $data['interests'] = array_fill_keys($interests,true);

        return ['json' => array_merge($model->getMailingListProfile(),$data)];
    }

    /**
     * @param $model
     * @param $subscriberDetails
     */
    protected function saveData($model, $subscriberDetails)
    {
        if ($mailingList = $model->mailingList) {
            $mailingList->data = json_encode($subscriberDetails);
            $mailingList->save();
            return;
        }
        $model->mailingList()->create(['data' => json_encode($subscriberDetails)]);
    }
}