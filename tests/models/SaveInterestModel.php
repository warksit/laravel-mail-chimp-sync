<?php namespace Warksit\LaravelMailChimpSync\Tests\models;

use Mockery as m;

class SaveInterestModel extends BaseInterestModel
{
    public function interest()
    {
        $morph = m::mock(\Illuminate\Database\Eloquent\Relations\MorphOne::class);
        $morph->shouldReceive('getResults')->andReturn(null);
        $morph->shouldReceive('save');
        return $morph;
    }
}