<?php

namespace Warksit\LaravelMailChimpSync\Tests\Unit;

use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Event;
use Warksit\LaravelMailChimpSync\Events\AddInterestFailed;
use Warksit\LaravelMailChimpSync\Jobs\AddInterestGroup;
use Warksit\LaravelMailChimpSync\Jobs\DeleteInterestGroup;
use Warksit\LaravelMailChimpSync\Jobs\SubscribeToMailingList;
use Warksit\LaravelMailChimpSync\Jobs\UnSubscribeFromMailingList;
use Warksit\LaravelMailChimpSync\Models\Interest;
use Warksit\LaravelMailChimpSync\Models\MailingList;
use Warksit\LaravelMailChimpSync\Tests\Fixtures\Group;
use Warksit\LaravelMailChimpSync\Tests\Fixtures\Member;
use Warksit\LaravelMailChimpSync\Tests\TestCase;

class JobsTest extends TestCase
{
    private function handle($job)
    {
        return $this->app->call([$job, 'handle']);
    }

    private function member(array $attributes = [])
    {
        return $this->withoutSyncing(fn () => Member::create($attributes + ['email' => 'a@example.com']));
    }

    public function test_subscribing_sends_the_member()
    {
        $this->mailChimpReplies($this->reply(['id' => 'aaa', 'unique_email_id' => 'bbb']));

        $this->handle(new SubscribeToMailingList($this->member()));

        $this->assertSame('PUT', $this->sent(0)->getMethod());
    }

    public function test_an_opted_out_member_is_not_subscribed()
    {
        $this->mailChimpReplies();

        $this->handle(new SubscribeToMailingList($this->member(['opted_out' => true])));

        $this->assertCount(0, $this->requests);
    }

    public function test_the_member_jobs_only_accept_member_models()
    {
        $this->expectException(\InvalidArgumentException::class);

        new SubscribeToMailingList($this->withoutSyncing(fn () => Group::create(['name' => 'Festival'])));
    }

    public function test_unsubscribing_deletes_the_stored_details_and_the_member()
    {
        $member = $this->member();
        $member->mailingList()->create(['data' => '{}']);
        $this->mailChimpReplies(new Response(204));

        $this->handle(new UnSubscribeFromMailingList($member, 'a@example.com'));

        $this->assertSame('DELETE', $this->sent(0)->getMethod());
        $this->assertSame(0, MailingList::count());
    }

    public function test_a_member_never_subscribed_is_not_unsubscribed()
    {
        $this->mailChimpReplies();

        $this->handle(new UnSubscribeFromMailingList($this->member(), 'a@example.com'));

        $this->assertCount(0, $this->requests);
    }

    public function test_adding_an_interest_over_the_limit_raises_an_event_instead_of_failing()
    {
        Event::fake([AddInterestFailed::class]);
        $group = $this->withoutSyncing(fn () => Group::create(['name' => 'Festival']));
        $this->mailChimpReplies($this->reply([
            'detail' => 'Cannot have more than 60 interests per list (across all categories).',
        ], 400));

        $this->handle(new AddInterestGroup($group));

        Event::assertDispatched(AddInterestFailed::class, fn ($event) => $event->model->is($group));
        $this->assertSame(0, Interest::count());
    }

    public function test_deleting_an_interest_group_removes_it()
    {
        $group = $this->withoutSyncing(fn () => Group::create(['name' => 'Festival']));
        $group->interest()->create(['interest_id' => 'abc']);
        $this->mailChimpReplies(new Response(204));

        $this->handle(new DeleteInterestGroup($group->fresh()));

        $this->assertSame('DELETE', $this->sent(0)->getMethod());
        $this->assertSame(0, Interest::count());
    }
}
