<?php namespace Warksit\LaravelMailChimpSync\Tests\unit;

use Mockery as m;
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
     * @group  */
    public function it_creates_interest_group()
    {
        $this->model = new SaveInterestModel();

        $response = m::mock(\Psr\Http\Message\ResponseInterface::class);

        $this->setAuth($apiKey = getenv('MAILCHIMP_API'));
       
        $this->guzzle->shouldReceive('request')->with(
            'POST',
            $this->endpoint . "/3.0/lists/{$this->model->getInterestListId()}/interest-categories/{$this->model->getInterestCategoryId()}/interests",
            [
                'headers' => [
                    'Authorization' => 'OAuth ' . $apiKey,
                ],
                'json' => [
                    'name' => $this->model->getInterestName(),
                    'display_order' => $this->model->getInterestDisplayOrder(),
                ],
            ]
        )->andReturn($response);

        $response->shouldReceive('getBody')->andReturn(json_encode([
            'id' => '1234',
        ]));

        $interest = m::mock(Interest::class);
        $interest->shouldReceive('setAttribute')->with('interest_id', 1234);
        (new InterestActions($this->guzzle, $this->config, $interest))->add($this->model);
    }

    /** @test
     * @group wip */
    public function it_deletes_interest_group()
    {
        $this->model = new DeleteInterestModel();

        $this->setAuth($apiKey = getenv('MAILCHIMP_API'));

        $this->guzzle->shouldReceive('request')->with(
            'DELETE',
            $this->endpoint . "/3.0/lists/{$this->model->getInterestListId()}/interest-categories/{$this->model->getInterestCategoryId()}/interests/{$this->model->interest->interest_id}",
            [
                'headers' => [
                    'Authorization' => 'OAuth ' . $apiKey,
                ]
            ]
        );

        $interest = m::mock(Interest::class);
        (new InterestActions($this->guzzle, $this->config, $interest))->remove($this->model);
    }
}
