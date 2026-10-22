<?php

namespace Warksit\LaravelMailChimpSync\Tests\Unit;

use Illuminate\Database\Eloquent\Model;
use Warksit\LaravelMailChimpSync\Exceptions\MailChimpException;
use Warksit\LaravelMailChimpSync\MailChimp\InterestActions;
use Warksit\LaravelMailChimpSync\Models\Interest;
use Warksit\LaravelMailChimpSync\Tests\Fixtures\Group;
use Warksit\LaravelMailChimpSync\Tests\TestCase;

class InterestActionsTest extends TestCase
{
    private $group;

    protected function setUp(): void
    {
        parent::setUp();

        $this->group = $this->withoutSyncing(fn () => Group::create(['name' => 'Annual Memberships']));
    }

    private function actions()
    {
        return new InterestActions($this->app->make(\GuzzleHttp\Client::class), new Interest);
    }

    private function interestsUri($suffix = '')
    {
        return $this->uri('/3.0/lists/list1/interest-categories/cat1/interests'.$suffix);
    }

    private function alreadyExists()
    {
        return $this->reply([
            'title' => 'Invalid Resource',
            'status' => 400,
            'detail' => 'Cannot add "Annual Memberships" because it already exists on the list.',
        ], 400);
    }

    private function interestsOnTheList()
    {
        return $this->reply(['interests' => [
            ['id' => 'aaa111', 'name' => 'Cheltenham Festival'],
            ['id' => '79ec395656', 'name' => 'Annual Memberships'],
        ]]);
    }

    public function test_it_creates_the_interest_and_remembers_its_id()
    {
        $this->mailChimpReplies($this->reply(['id' => 'new123']));

        $this->actions()->add($this->group);

        $this->assertSame('POST', $this->sent(0)->getMethod());
        $this->assertSame($this->interestsUri(), (string) $this->sent(0)->getUri());
        $this->assertSame(['name' => 'Annual Memberships', 'display_order' => 3], $this->sentJson(0));
        $this->assertSame('new123', $this->group->fresh()->interest->interest_id);
    }

    public function test_it_updates_an_interest_it_already_has()
    {
        $this->group->interest()->create(['interest_id' => 'old456']);
        $group = $this->group->fresh();
        $group->name = 'Annual Members';
        $this->mailChimpReplies($this->reply(['id' => 'old456']));

        $this->actions()->add($group);

        $this->assertCount(1, $this->requests);
        $this->assertSame('PATCH', $this->sent(0)->getMethod());
        $this->assertSame($this->interestsUri('/old456'), (string) $this->sent(0)->getUri());
        $this->assertSame(['name' => 'Annual Members', 'display_order' => 3], $this->sentJson(0));
        $this->assertSame(1, Interest::count());
    }

    public function test_it_adopts_the_existing_interest_when_the_name_is_already_on_the_list()
    {
        $this->mailChimpReplies($this->alreadyExists(), $this->interestsOnTheList());

        $this->actions()->add($this->group);

        $this->assertSame('GET', $this->sent(1)->getMethod());
        $this->assertSame($this->interestsUri('?count=1000'), (string) $this->sent(1)->getUri());
        $this->assertSame('79ec395656', $this->group->fresh()->interest->interest_id);
    }

    public function test_it_matches_the_name_regardless_of_case()
    {
        $this->group->name = 'annual memberships';
        $this->mailChimpReplies($this->alreadyExists(), $this->interestsOnTheList());

        $this->actions()->add($this->group);

        $this->assertSame('79ec395656', $this->group->fresh()->interest->interest_id);
    }

    public function test_it_will_not_adopt_an_interest_another_model_holds()
    {
        // Deleting either model would delete the interest from under the other.
        $other = $this->withoutSyncing(fn () => Group::create(['name' => 'Annual Memberships']));
        $other->interest()->create(['interest_id' => '79ec395656']);
        $this->mailChimpReplies($this->alreadyExists(), $this->interestsOnTheList());

        try {
            $this->actions()->add($this->group);
            $this->fail('Expected the refusal to be rethrown');
        } catch (MailChimpException $e) {
            $this->assertStringContainsString('already exists on the list', $e->getMessage());
        }

        $this->assertNull($this->group->fresh()->interest);
    }

    public function test_it_rethrows_when_the_list_has_no_interest_of_that_name()
    {
        $this->mailChimpReplies($this->alreadyExists(), $this->reply(['interests' => []]));

        $this->expectException(MailChimpException::class);

        $this->actions()->add($this->group);
    }

    public function test_it_does_not_look_the_name_up_for_any_other_refusal()
    {
        $this->mailChimpReplies($this->reply(['detail' => 'Something else went wrong.'], 400));

        try {
            $this->actions()->add($this->group);
            $this->fail('Expected the refusal to be rethrown');
        } catch (MailChimpException $e) {
            $this->assertStringContainsString('Something else went wrong', $e->getMessage());
        }

        $this->assertCount(1, $this->requests);
    }

    public function test_it_deletes_the_interest_and_forgets_it()
    {
        $this->group->interest()->create(['interest_id' => 'old456']);
        $this->mailChimpReplies(new \GuzzleHttp\Psr7\Response(204));

        $this->actions()->remove($this->group->fresh());

        $this->assertSame('DELETE', $this->sent(0)->getMethod());
        $this->assertSame($this->interestsUri('/old456'), (string) $this->sent(0)->getUri());
        $this->assertSame(0, Interest::count());
    }

    public function test_removing_a_model_with_no_interest_sends_nothing()
    {
        $this->mailChimpReplies();

        $this->actions()->remove($this->group);

        $this->assertCount(0, $this->requests);
    }

    public function test_it_only_accepts_interest_models()
    {
        $this->mailChimpReplies();

        $this->expectException(\InvalidArgumentException::class);

        $this->actions()->add(new class extends Model {});
    }
}
