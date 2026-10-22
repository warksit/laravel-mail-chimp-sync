<?php

namespace Warksit\LaravelMailChimpSync\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Warksit\LaravelMailChimpSync\EloquentTraits\MailChimpSyncMember;
use Warksit\LaravelMailChimpSync\Interfaces\CanSyncMailChimpMember;

class Member extends Model implements CanSyncMailChimpMember
{
    use MailChimpSyncMember;

    protected $guarded = [];

    protected $casts = ['opted_out' => 'boolean'];

    public $interestIds = [];

    public function getMailingListId()
    {
        return MailChimpAccount::$listId;
    }

    public function getMailingListEmail()
    {
        return $this->email;
    }

    public function getMailingListEmailField()
    {
        return 'email';
    }

    public function getMailingListProfile()
    {
        return ['merge_fields' => ['FNAME' => $this->first_name]];
    }

    public function getMailingListOptedOut()
    {
        return $this->opted_out;
    }

    public function getMailingListInterests()
    {
        return $this->interestIds;
    }

    public function getMailChimpAuth()
    {
        return MailChimpAccount::auth();
    }
}
