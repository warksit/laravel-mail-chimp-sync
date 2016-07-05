<?php namespace Warksit\LaravelMailChimpSync\Tests\models;

use Illuminate\Database\Eloquent\Model;
use Warksit\LaravelMailChimpSync\MailChimp\MailChimpAuth;
use Warksit\LaravelMailChimpSync\Interfaces\CanSyncMailChimpInterest;

class BaseInterestModel extends Model implements CanSyncMailChimpInterest
{

    private $api;

    public function getInterestDisplayOrder()
    {
        return 0;
    }

    public function getInterestName()
    {
        return 'Name of Interest';
    }

    public function getInterestListId()
    {
        return getenv('MAILCHIMP_LIST_ID');
    }

    public function getInterestCategoryId()
    {
        return getenv('MAILCHIMP_INTEREST_CATEGORY_ID');
    }

    /**
     * MailChimp Auth details
     * return new MailChimpAuth($endpoint, $api_key);
     * @return MailChimpAuth
     */
    public function getMailChimpAuth()
    {
        return new MailChimpAuth(
            getenv('MAILCHIMP_ENDPOINT'),
            $this->api ? $this->api : getenv('MAILCHIMP_API')
        );
    }

    public function setApi($api)
    {
        $this->api = $api;
    }
}