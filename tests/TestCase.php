<?php namespace Warksit\LaravelMailChimpSync\Tests;

use Dotenv\Dotenv;
use GuzzleHttp\Client;
use Illuminate\Contracts\Config\Repository;
use Mockery as m;

class TestCase extends \PHPUnit_Framework_TestCase
{
    protected $config;
    protected $endpoint;
    protected $guzzle;

    public function setUp()
    {

        $dotenv = new Dotenv(__DIR__.'/../');
        $dotenv->load();
        $this->config = m::mock(Repository::class);
        $this->guzzle = m::mock(Client::class);
        $this->endpoint = getenv('MAILCHIMP_ENDPOINT');
    }

    public function tearDown()
    {
        m::close();
    }

    /**
     * @param $apiKey
     */
    protected function setAuth($apiKey)
    {
        $this->config->shouldReceive('get')->with('maillist.default')->andReturn('basic');
        $this->config->shouldReceive('get')->with('maillist.auths.basic.api_key')->andReturn($apiKey);
        $this->config->shouldReceive('get')->with('maillist.endpoint')->andReturn($this->endpoint);
    }
}