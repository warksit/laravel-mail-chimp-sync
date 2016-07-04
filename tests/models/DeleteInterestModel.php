<?php namespace Warksit\LaravelMailChimpSync\Tests\models;

use Mockery as m;
use Warksit\LaravelMailChimpSync\Models\Interest;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class DeleteInterestModel extends BaseInterestModel
{
    private $interest_id;

    public function interest()
    {
        $interest = m::mock(Interest::class);
        $interest->shouldReceive('getAttribute')->with('interest_id')->andReturn($this->interest_id);
        $interest->shouldReceive('delete');

        $morph = m::mock(MorphOne::class);
        $morph->shouldReceive('getResults')->andReturn($interest);
        return $morph;
    }

    public function setInterestId($value)
    {
        $this->interest_id = $value;
    }

}