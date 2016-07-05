<?php namespace Warksit\LaravelMailChimpSync\Tests\models;

use Mockery as m;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Warksit\LaravelMailChimpSync\Models\MailingList;
use Warksit\LaravelMailChimpSync\MailChimp\MailChimpAuth;
use Warksit\LaravelMailChimpSync\Interfaces\CanSyncMailChimpMember;

class ListModel extends Model implements CanSyncMailChimpMember
{

    public function mailingList()
    {

        $mailingList = m::mock(MailingList::class);
        $mailingList->shouldReceive('setAttribute')->with('data', m::any());
        $mailingList->shouldReceive('save');
        $morph = m::mock(MorphOne::class);
        $morph->shouldReceive('getResults')->andReturn($mailingList);

        return $morph;
    }

    public function getMailingListId()
    {
        return getenv('MAILCHIMP_LIST_ID');
    }

    public function getMailingListEmail()
    {
        return "a@warks.it";
    }

    public function getMailingListProfile()
    {
        return [
            'FNAME' => 'Andrew',
            'LNAME' => 'Westrope'
        ];
    }

    public function getMailingListOptedOut()
    {
        return false;
    }

    public function getInterestCategoryId()
    {
        return getenv('MAILCHIMP_INTEREST_CATEGORY_ID');
    }

    public function getMailingListEmailField()
    {
        return 'email';
    }

    public function getMailingListInterests()
    {
        return [];
    }

    public function getMailChimpAuth()
    {
        return new MailChimpAuth(getenv('MAILCHIMP_ENDPOINT'), getenv('MAILCHIMP_API'));
    }
}