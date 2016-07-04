<?php namespace Warksit\LaravelMailChimpSync\Tests\integration;

use Mockery as m;
use GuzzleHttp\Client;
use Warksit\LaravelMailChimpSync\Tests\TestCase;
use Warksit\LaravelMailChimpSync\Models\Interest;
use Warksit\LaravelMailChimpSync\MailChimp\InterestActions;
use Warksit\LaravelMailChimpSync\Tests\models\SaveInterestModel;
use Warksit\LaravelMailChimpSync\Tests\models\DeleteInterestModel;

class InterestActionsTest extends TestCase
{
    private $model;

    public function setUp()
    {
        parent::setUp();
    }
    /** @test
     * @group */
    public function it_creates_interest_group_with_mailChimp()
    {
        $this->model = new SaveInterestModel();
        $this->setAuth($apiKey = getenv('MAILCHIMP_API'));
        $interest = m::mock(Interest::class);
        $interest->shouldReceive('setAttribute')->with('interest_id', m::any());
        return (new InterestActions(new Client(), $this->config, $interest))->add($this->model);
    }


    /** @test
     * @group
     * @depends it_creates_interest_group_with_mailChimp */
    public function it_deletes_interest_group_with_mailChimp($value)
    {
        $this->model = new DeleteInterestModel();
        $this->model->setInterestId($value);
        $this->setAuth($apiKey = getenv('MAILCHIMP_API'));
        $interest = m::mock(Interest::class);
        (new InterestActions(new Client(), $this->config, $interest))->remove($this->model);
    }
    
}
