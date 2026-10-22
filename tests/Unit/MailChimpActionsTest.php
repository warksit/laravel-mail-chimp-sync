<?php

namespace Warksit\LaravelMailChimpSync\Tests\Unit;

use GuzzleHttp\Client;
use Warksit\LaravelMailChimpSync\Exceptions\MailChimpAuthNotSet;
use Warksit\LaravelMailChimpSync\Exceptions\MailChimpException;
use Warksit\LaravelMailChimpSync\Exceptions\MailChimpTooManyInterestsException;
use Warksit\LaravelMailChimpSync\MailChimp\MailChimpActions;
use Warksit\LaravelMailChimpSync\Tests\Fixtures\Group;
use Warksit\LaravelMailChimpSync\Tests\TestCase;

class MailChimpActionsTest extends TestCase
{
    public function test_it_pings_the_api_root_with_the_token()
    {
        $client = $this->mailChimpReplies($this->reply(['account_id' => 'abc']));

        $response = (new MailChimpActions($client))->ping(new Group);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('GET', $this->sent(0)->getMethod());
        $this->assertSame($this->uri('/3.0/'), (string) $this->sent(0)->getUri());
        $this->assertSame('OAuth token', $this->sent(0)->getHeaderLine('Authorization'));
    }

    public function test_it_refuses_to_send_before_auth_is_set()
    {
        $actions = new class(new Client) extends MailChimpActions
        {
            public function send()
            {
                return $this->process('GET', '/3.0/');
            }
        };

        $this->expectException(MailChimpAuthNotSet::class);

        $actions->send();
    }

    public function test_a_refusal_names_the_request_and_carries_mailchimps_reason()
    {
        $client = $this->mailChimpReplies($this->reply(['title' => 'API Key Invalid'], 401));

        $this->expectException(MailChimpException::class);
        $this->expectExceptionMessage('Error: GETing to '.$this->uri('/3.0/').' due to {"title":"API Key Invalid"}');

        (new MailChimpActions($client))->ping(new Group);
    }

    public function test_the_interest_limit_has_its_own_exception()
    {
        $client = $this->mailChimpReplies($this->reply([
            'detail' => 'Cannot have more than 60 interests per list (across all categories).',
        ], 400));

        $this->expectException(MailChimpTooManyInterestsException::class);

        (new MailChimpActions($client))->ping(new Group);
    }
}
