<?php

namespace Warksit\LaravelMailChimpSync\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Warksit\LaravelMailChimpSync\EloquentTraits\MailChimpSyncInterest;
use Warksit\LaravelMailChimpSync\Interfaces\CanSyncMailChimpInterest;

class Group extends Model implements CanSyncMailChimpInterest
{
    use MailChimpSyncInterest;

    protected $guarded = [];

    protected $casts = ['sync' => 'boolean'];

    protected $attributes = ['sync' => true];

    public function shouldSync()
    {
        return $this->sync;
    }

    public function getInterestListId()
    {
        return MailChimpAccount::$listId;
    }

    public function getInterestCategoryId()
    {
        return MailChimpAccount::$categoryId;
    }

    public function getInterestDisplayOrder()
    {
        return 3;
    }

    public function getInterestName()
    {
        return $this->name;
    }

    public function getMailChimpAuth()
    {
        return MailChimpAccount::auth();
    }
}
