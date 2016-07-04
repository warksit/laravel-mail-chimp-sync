<?php namespace Warksit\LaravelMailChimpSync\Models;

use Illuminate\Database\Eloquent\Model;

class Interest extends Model
{
    protected $fillable = ['interest_id'];

    public function interestable()
    {
        return $this->morphTo();
    }
}