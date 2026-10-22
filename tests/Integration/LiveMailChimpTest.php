<?php

namespace Warksit\LaravelMailChimpSync\Tests\Integration;

use Dotenv\Dotenv;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use Warksit\LaravelMailChimpSync\MailChimp\InterestActions;
use Warksit\LaravelMailChimpSync\MailChimp\MailChimpActions;
use Warksit\LaravelMailChimpSync\MailChimp\SubscriptionActions;
use Warksit\LaravelMailChimpSync\Models\Interest;
use Warksit\LaravelMailChimpSync\Tests\Fixtures\Group;
use Warksit\LaravelMailChimpSync\Tests\Fixtures\MailChimpAccount;
use Warksit\LaravelMailChimpSync\Tests\Fixtures\Member;
use Warksit\LaravelMailChimpSync\Tests\TestCase;

/**
 * Against the real API, with the account in .env (see .env.example). Every test
 * cleans up what it creates on the list.
 */
class LiveMailChimpTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Dotenv::createImmutable(dirname(__DIR__, 2))->safeLoad();

        foreach (['MAILCHIMP_ENDPOINT', 'MAILCHIMP_API', 'MAILCHIMP_LIST_ID', 'MAILCHIMP_INTEREST_CATEGORY_ID'] as $key) {
            if (empty($_ENV[$key])) {
                $this->markTestSkipped("$key is not set in .env");
            }
        }

        MailChimpAccount::$endpoint = $_ENV['MAILCHIMP_ENDPOINT'];
        MailChimpAccount::$token = $_ENV['MAILCHIMP_API'];
        MailChimpAccount::$listId = $_ENV['MAILCHIMP_LIST_ID'];
        MailChimpAccount::$categoryId = $_ENV['MAILCHIMP_INTEREST_CATEGORY_ID'];
    }

    public function test_it_authenticates()
    {
        $response = (new MailChimpActions(new Client))->ping(new Group);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_an_interest_can_be_created_renamed_adopted_and_deleted()
    {
        $name = 'Package test '.Str::random(8);
        $group = $this->withoutSyncing(fn () => Group::create(['name' => $name]));

        (new InterestActions(new Client, new Interest))->add($group);
        $id = $group->fresh()->interest->interest_id;

        try {
            $group = $group->fresh();
            $group->name = $name.' renamed';
            (new InterestActions(new Client, new Interest))->add($group);

            // Lose the row, as meeting 107 did, and sync again under the same name.
            $group->interest->delete();
            (new InterestActions(new Client, new Interest))->add($group->fresh());

            $this->assertSame($id, $group->fresh()->interest->interest_id);
        } finally {
            (new InterestActions(new Client, new Interest))->remove($group->fresh());
        }

        $this->assertSame(0, Interest::count());
    }

    public function test_a_member_can_be_subscribed_and_unsubscribed()
    {
        if (empty($_ENV['MAILCHIMP_TEST_EMAIL'])) {
            $this->markTestSkipped('MAILCHIMP_TEST_EMAIL is not set in .env');
        }

        $member = $this->withoutSyncing(fn () => Member::create(['email' => $_ENV['MAILCHIMP_TEST_EMAIL'], 'first_name' => 'Test']));
        $actions = new SubscriptionActions(new Client);

        $details = $actions->subscribe($member);

        $this->assertNotEmpty($details->id);
        $this->assertTrue($actions->unSubscribe($member, $member->email));
    }
}
