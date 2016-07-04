<?php namespace Warksit\LaravelMailChimpSync\Tests\integration;

use Mockery as m;
use GuzzleHttp\Client;
use Warksit\LaravelMailChimpSync\Tests\TestCase;
use Warksit\LaravelMailChimpSync\MailChimp\MailChimpActions;
use Warksit\LaravelMailChimpSync\Exceptions\MailChimpException;

class AuthWithMailChimpTest extends TestCase
{

    /** @test
     * @group live */
    public function it_connect_with_basic_auth()
    {
        $this->setAuth($apiKey = getenv('MAILCHIMP_API'));

        $ma = new MailChimpActions(new Client(),$this->config);
        $result = $ma->ping();

        $this->assertEquals(200, $result->getStatusCode());
    }

    /** @test
     * @group live */
    public function it_connect_with_oauth_auth()
    {

        $this->setAuth(getenv('MAILCHIMP_API'));

        $ma = new MailChimpActions(new Client(),$this->config);
        $result = $ma->ping();

        $this->assertEquals(200, $result->getStatusCode());
    }

    /** @test
     * @group live */
    public function it_fails_to_connect_with_invalid_token()
    {
        $this->setAuth('thisWillNotWork!');

        $this->expectException(MailChimpException::class);
        $ma = new MailChimpActions(new Client(),$this->config);

        $ma->ping();
    }
}