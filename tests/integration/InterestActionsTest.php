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
    /** @test
     * @group */
    public function it_creates_interest_group_with_mailChimp()
    {
        $model = new SaveInterestModel();
        $interest = m::mock(Interest::class);
        $interest->shouldReceive('setAttribute')->with('interest_id', m::any());

        $ia = new InterestActions(new Client(), $interest);
        return $ia->add($model);
    }


    /** @test
     * @group
     * @depends it_creates_interest_group_with_mailChimp */
    public function it_deletes_interest_group_with_mailChimp($value)
    {
        $model = new DeleteInterestModel();
        $model->setInterestId($value);
        $interest = m::mock(Interest::class);

        $ia = new InterestActions(new Client(), $interest);
        $ia->remove($model);
    }
    
}
