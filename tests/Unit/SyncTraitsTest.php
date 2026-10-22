<?php

namespace Warksit\LaravelMailChimpSync\Tests\Unit;

use Illuminate\Support\Facades\Bus;
use Warksit\LaravelMailChimpSync\Jobs\AddInterestGroup;
use Warksit\LaravelMailChimpSync\Jobs\DeleteInterestGroup;
use Warksit\LaravelMailChimpSync\Jobs\SubscribeToMailingList;
use Warksit\LaravelMailChimpSync\Jobs\UnSubscribeFromMailingList;
use Warksit\LaravelMailChimpSync\MailingList;
use Warksit\LaravelMailChimpSync\Tests\Fixtures\Group;
use Warksit\LaravelMailChimpSync\Tests\Fixtures\Member;
use Warksit\LaravelMailChimpSync\Tests\TestCase;

class SyncTraitsTest extends TestCase
{
    private function emailToDelete(UnSubscribeFromMailingList $job)
    {
        return (fn () => $this->emailToDelete)->call($job);
    }

    public function test_saving_a_member_subscribes_them()
    {
        Bus::fake();

        Member::create(['email' => 'a@example.com']);

        Bus::assertDispatched(SubscribeToMailingList::class);
        Bus::assertNotDispatched(UnSubscribeFromMailingList::class);
    }

    public function test_changing_a_members_email_unsubscribes_the_old_address()
    {
        $member = $this->withoutSyncing(fn () => Member::create(['email' => 'old@example.com']));
        Bus::fake();

        $member->update(['email' => 'new@example.com']);

        Bus::assertDispatched(UnSubscribeFromMailingList::class, fn ($job) => $this->emailToDelete($job) === 'old@example.com');
        Bus::assertDispatched(SubscribeToMailingList::class);
    }

    public function test_saving_other_changes_keeps_the_address()
    {
        $member = $this->withoutSyncing(fn () => Member::create(['email' => 'a@example.com']));
        Bus::fake();

        $member->update(['first_name' => 'Andrew']);

        Bus::assertNotDispatched(UnSubscribeFromMailingList::class);
        Bus::assertDispatched(SubscribeToMailingList::class);
    }

    public function test_deleting_a_member_unsubscribes_them()
    {
        $member = $this->withoutSyncing(fn () => Member::create(['email' => 'a@example.com']));
        Bus::fake();

        $member->delete();

        Bus::assertDispatched(UnSubscribeFromMailingList::class, fn ($job) => $this->emailToDelete($job) === 'a@example.com');
    }

    public function test_saving_a_group_adds_its_interest()
    {
        Bus::fake();

        Group::create(['name' => 'Festival']);

        Bus::assertDispatched(AddInterestGroup::class);
        Bus::assertNotDispatched(DeleteInterestGroup::class);
    }

    public function test_a_group_that_should_not_sync_has_its_interest_removed()
    {
        Bus::fake();

        Group::create(['name' => 'Festival', 'sync' => false]);

        Bus::assertDispatched(DeleteInterestGroup::class);
        Bus::assertNotDispatched(AddInterestGroup::class);
    }

    public function test_deleting_a_group_removes_its_interest()
    {
        $group = $this->withoutSyncing(fn () => Group::create(['name' => 'Festival']));
        Bus::fake();

        $group->delete();

        Bus::assertDispatched(DeleteInterestGroup::class);
    }

    public function test_nothing_is_sent_while_the_mailing_list_is_disabled()
    {
        Bus::fake();
        $this->app->make(MailingList::class)->disable();

        $member = Member::create(['email' => 'a@example.com']);
        $member->update(['email' => 'b@example.com']);
        $member->delete();
        $group = Group::create(['name' => 'Festival']);
        $group->delete();

        Bus::assertNothingDispatched();
    }

    public function test_the_config_can_start_it_disabled()
    {
        config(['mailinglist.enabled' => false]);
        $this->app->forgetInstance(MailingList::class);

        $this->assertFalse($this->app->make(MailingList::class)->enabled());
    }
}
