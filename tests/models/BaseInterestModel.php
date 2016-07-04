<?php namespace Warksit\LaravelMailChimpSync\Tests\models;

use Illuminate\Database\Eloquent\Model;
use Warksit\LaravelMailChimpSync\Interfaces\CanSyncMailChimpInterest;

class BaseInterestModel extends Model implements CanSyncMailChimpInterest
{

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
}