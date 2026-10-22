<?php

namespace Warksit\LaravelMailChimpSync\Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Warksit\LaravelMailChimpSync\MailChimp\SubscriptionActions;
use Warksit\LaravelMailChimpSync\Models\MailingList;
use Warksit\LaravelMailChimpSync\Tests\Fixtures\Member;
use Warksit\LaravelMailChimpSync\Tests\TestCase;

class SubscriptionActionsTest extends TestCase
{
    private $member;

    protected function setUp(): void
    {
        parent::setUp();

        $this->member = $this->withoutSyncing(fn () => Member::create([
            'email' => 'Andrew@Example.com',
            'first_name' => 'Andrew',
        ]));
    }

    private function actions()
    {
        return new SubscriptionActions($this->app->make(Client::class));
    }

    private function memberUri($email)
    {
        return $this->uri('/3.0/lists/list1/members/'.md5(strtolower($email)));
    }

    private function subscribed()
    {
        return $this->reply(['id' => 'aaa', 'unique_email_id' => 'bbb']);
    }

    public function test_it_puts_the_member_at_the_hash_of_their_lowercased_email()
    {
        $this->mailChimpReplies($this->subscribed());

        $this->actions()->subscribe($this->member);

        $this->assertSame('PUT', $this->sent(0)->getMethod());
        $this->assertSame($this->memberUri('andrew@example.com'), (string) $this->sent(0)->getUri());
        $this->assertSame([
            'merge_fields' => ['FNAME' => 'Andrew'],
            'status_if_new' => 'pending',
            'email_address' => 'Andrew@Example.com',
        ], $this->sentJson(0));
    }

    public function test_it_sends_the_members_interests()
    {
        $this->member->interestIds = ['abc', 'def'];
        $this->mailChimpReplies($this->subscribed());

        $this->actions()->subscribe($this->member);

        $this->assertSame(['abc' => true, 'def' => true], $this->sentJson(0)['interests']);
    }

    public function test_it_keeps_what_mailchimp_returns()
    {
        $this->mailChimpReplies($this->subscribed());

        $details = $this->actions()->subscribe($this->member);

        $this->assertSame('aaa', $details->id);
        $this->assertSame('bbb', $details->unique_email_id);
        $this->assertSame(
            ['id' => 'aaa', 'unique_email_id' => 'bbb'],
            json_decode($this->member->fresh()->mailingList->data, true)
        );
    }

    public function test_subscribing_again_updates_the_stored_details()
    {
        $this->mailChimpReplies($this->subscribed(), $this->reply(['id' => 'aaa', 'unique_email_id' => 'ccc']));

        $this->actions()->subscribe($this->member);
        $this->actions()->subscribe($this->member->fresh());

        $this->assertSame(1, MailingList::count());
        $this->assertSame('ccc', json_decode(MailingList::first()->data)->unique_email_id);
    }

    public function test_it_deletes_the_given_address()
    {
        $this->mailChimpReplies(new Response(204));

        $this->assertTrue($this->actions()->unSubscribe($this->member, 'Old@Example.com'));

        $this->assertSame('DELETE', $this->sent(0)->getMethod());
        $this->assertSame($this->memberUri('old@example.com'), (string) $this->sent(0)->getUri());
    }

    public function test_it_only_accepts_member_models()
    {
        $this->mailChimpReplies();

        $this->expectException(\InvalidArgumentException::class);

        $this->actions()->subscribe(new class extends \Illuminate\Database\Eloquent\Model {});
    }
}
