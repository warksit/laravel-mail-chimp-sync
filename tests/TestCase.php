<?php namespace Warksit\LaravelMailChimpSync\Tests;

use Dotenv\Dotenv;
use GuzzleHttp\Client;
use Illuminate\Contracts\Config\Repository;
use Mockery as m;
use Warksit\LaravelMailChimpSync\MailChimp\MailChimpAuth;

class TestCase extends \PHPUnit_Framework_TestCase
{
    protected $config;
    protected $endpoint;
    protected $guzzle;

    public function setUp()
    {

        $dotenv = new Dotenv(__DIR__.'/../');
        $dotenv->load();
        $this->guzzle = m::mock(Client::class);
        $this->endpoint = getenv('MAILCHIMP_ENDPOINT');
    }

    public function tearDown()
    {
        m::close();
    }

    /**
     * @param $apiKey
     * @return MailChimpAuth
     */
    protected function setAuth($apiKey)
    {
        return new MailChimpAuth($this->endpoint, $apiKey);
    }
}