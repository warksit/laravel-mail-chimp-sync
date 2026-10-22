<?php

namespace Warksit\LaravelMailChimpSync\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as Orchestra;
use Psr\Http\Message\RequestInterface;
use Warksit\LaravelMailChimpSync\MailingList;
use Warksit\LaravelMailChimpSync\MailingListServiceProvider;
use Warksit\LaravelMailChimpSync\Tests\Fixtures\MailChimpAccount;

abstract class TestCase extends Orchestra
{
    use RefreshDatabase;

    /**
     * Every request the mocked client has sent, oldest first.
     *
     * @var array
     */
    protected $requests = [];

    protected function getPackageProviders($app)
    {
        return [MailingListServiceProvider::class];
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(__DIR__.'/../migrations');
        $this->loadMigrationsFrom(__DIR__.'/Fixtures/migrations');
    }

    protected function tearDown(): void
    {
        MailChimpAccount::reset();

        parent::tearDown();
    }

    /**
     * Answer the next requests to MailChimp with these responses, in order, and bind
     * the client into the container so the jobs resolve it too. A 4xx response
     * throws as it would from the real API.
     */
    protected function mailChimpReplies(Response ...$responses)
    {
        $stack = HandlerStack::create(new MockHandler($responses));
        $stack->push(Middleware::history($this->requests));

        $client = new Client(['handler' => $stack]);
        $this->app->instance(Client::class, $client);

        return $client;
    }

    protected function reply($body, $status = 200)
    {
        return new Response($status, ['Content-Type' => 'application/json'], json_encode($body));
    }

    /**
     * @return RequestInterface
     */
    protected function sent($index)
    {
        return $this->requests[$index]['request'];
    }

    protected function sentJson($index)
    {
        return json_decode((string) $this->sent($index)->getBody(), true);
    }

    protected function uri($path)
    {
        return MailChimpAccount::$endpoint.$path;
    }

    /**
     * Create fixtures without the sync traits dispatching anything for them.
     *
     * Model::withoutEvents() is no good for this: a model booted inside it registers
     * its trait listeners on no dispatcher at all, and they never fire again.
     */
    protected function withoutSyncing(callable $callback)
    {
        $mailingList = $this->app->make(MailingList::class);
        $mailingList->disable();

        try {
            return $callback();
        } finally {
            $mailingList->enable();
        }
    }
}
