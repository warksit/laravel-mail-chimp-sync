<?php namespace Warksit\LaravelMailChimpSync\Tests\unit;

use Mockery as m;
use Warksit\LaravelMailChimpSync\MailChimp\MailChimpAuth;
use Warksit\LaravelMailChimpSync\Tests\TestCase;
use Warksit\LaravelMailChimpSync\Tests\models\ListModel;
use Warksit\LaravelMailChimpSync\MailChimp\SubscriptionActions;

class SubscriptionActionsTest extends TestCase
{
    private $model;

    public function setUp()
    {
        parent::setUp();
        $this->model = new ListModel();
    }
    /**
     * @test
     * @group  */
    public function it_subscribes_to_mailChimp()
    {
        $apiKey = getenv('MAILCHIMP_API');

        $response = m::mock(\Psr\Http\Message\ResponseInterface::class);

        $this->guzzle->shouldReceive('request')->with(
            'PUT',
            $this->endpoint . "/3.0/lists/{$this->model->getMailingListId()}/members/".md5(strtolower($email = $this->model->getMailingListEmail())),
            [
                'headers' => [
                    'Authorization' => 'OAuth ' . $apiKey,
                ],
                'json' => array_merge($this->model->getMailingListProfile(),[
                    'status' => 'subscribed',
                    'email_address' => $email,
                ]),
            ]
        )->andReturn($response);

        $response->shouldReceive('getBody')->andReturn(json_encode([
            'id' => 'aaa', 'unique_email_id'=> 'bbb'
        ]));

        $sa = new SubscriptionActions($this->guzzle);
        $mc = $sa->subscribe($this->model);

        $this->assertEquals('aaa',$mc->id);
        $this->assertEquals('bbb',$mc->unique_email_id);
    }

    /**
     * @test
     * @group  */
    public function it_unsubscribes_to_mailChimp()
    {
        $this->setAuth($apiKey = getenv('MAILCHIMP_API'));

        $this->guzzle->shouldReceive('request')->with(
            'DELETE',
            $this->endpoint . "/3.0/lists/{$this->model->getMailingListId()}/members/".md5(strtolower($email = $this->model->getMailingListEmail())),
            [
                'headers' => [
                    'Authorization' => 'OAuth ' . $apiKey,
                ],
            ]
        );

        $sa = new SubscriptionActions($this->guzzle, $this->config);

        $this->assertEquals(true, $sa->unsubscribe($this->model, $this->model->getMailingListEmail()));
    }
    
}


