<?php namespace Warksit\LaravelMailChimpSync\Tests\integration;

use Mockery as m;
use GuzzleHttp\Client;
use Warksit\LaravelMailChimpSync\Tests\models\BaseInterestModel;
use Warksit\LaravelMailChimpSync\Tests\TestCase;
use Warksit\LaravelMailChimpSync\MailChimp\MailChimpActions;
use Warksit\LaravelMailChimpSync\Exceptions\MailChimpException;

class AuthWithMailChimpTest extends TestCase
{
    private $model;
    /**
     * AuthWithMailChimpTest constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->model = new BaseInterestModel();
    }

    /** @test
     * @group  */
    public function it_connect_with_basic_auth()
    {
        $ma = new MailChimpActions(new Client());
        $this->model->setApi(getenv('MAILCHIMP_ACCESS_TOKEN'));

        $result = $ma->ping($this->model);

        $this->assertEquals(200, $result->getStatusCode());
    }

    /** @test
     * @group  */
    public function it_connect_with_oauth_auth()
    {
        $ma = new MailChimpActions(new Client(),$this->config);        
        $this->model->setApi(getenv('MAILCHIMP_API'));

        $result = $ma->ping($this->model);

        $this->assertEquals(200, $result->getStatusCode());
    }

    /** @test
     * @group  */
    public function it_fails_to_connect_with_invalid_token()
    {
        $this->expectException(MailChimpException::class);
        $this->model->setApi('thisWillNotWork!');
        $ma = new MailChimpActions(new Client(),$this->config);

        $result = $ma->ping($this->model);
    }
}