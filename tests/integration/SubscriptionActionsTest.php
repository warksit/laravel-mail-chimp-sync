<?php namespace Warksit\LaravelMailChimpSync\Tests\integration;

use Mockery as m;
use GuzzleHttp\Client;
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

    /** @test
     * @group  */
    public function it_subscribes_to_mailChimp()
    {
        $sa = new SubscriptionActions(new Client());

        $mc = $sa->subscribe($this->model);

        $this->assertEquals('d7858eaa2088ffd13a2c5e687ee68437',$mc->id);
        $this->assertEquals('0a12b6a14e',$mc->unique_email_id);
    }

    /** @test
     * @group  */
    public function it_updates_to_mailChimp()
    {
        $sa = new SubscriptionActions(new Client());

        $mc = $sa->subscribe($this->model);

        $this->assertEquals('d7858eaa2088ffd13a2c5e687ee68437',$mc->id);
        $this->assertEquals('0a12b6a14e',$mc->unique_email_id);
    }

    /** @test
     * @group  */
    public function it_adds_interest_to_mailChimp()
    {
        $sa = new SubscriptionActions(new Client());

        $mc = $sa->subscribe($this->model,'79d4b084e8');

        $this->assertEquals('d7858eaa2088ffd13a2c5e687ee68437',$mc->id);
        $this->assertEquals('0a12b6a14e',$mc->unique_email_id);
    }

    /** @test
     * @group  */
    public function it_adds_different_interest_to_mailChimp()
    {
        $sa = new SubscriptionActions(new Client());

        $mc = $sa->subscribe($this->model,'cdd3faeb0b');

        $this->assertEquals('d7858eaa2088ffd13a2c5e687ee68437',$mc->id);
        $this->assertEquals('0a12b6a14e',$mc->unique_email_id);
    }

    /** @test
     * @group  */
    public function check()
    {
        $sa = new SubscriptionActions(new Client());

        $mc = $sa->subscribe($this->model);

        $this->assertEquals('d7858eaa2088ffd13a2c5e687ee68437',$mc->id);
        $this->assertEquals('0a12b6a14e',$mc->unique_email_id);
    }

    /** @test
     * @group  */
    public function it_un_subscribes_from_mailChimp()
    {
        $sa = new SubscriptionActions(new Client());

        $this->assertEquals(true, $sa->unsubscribe($this->model, $this->model->getMailingListEmail()));
    }
}


