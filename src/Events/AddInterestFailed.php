<?php namespace Warksit\LaravelMailChimpSync\Events;

use Illuminate\Database\Eloquent\Model;

class AddInterestFailed
{
    /**
     * @var Model
     */
    public $model;

    /**
     * AddInterestFailed constructor.
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }
}