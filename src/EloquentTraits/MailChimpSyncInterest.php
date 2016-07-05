<?php namespace Warksit\LaravelMailChimpSync\EloquentTraits;


use Illuminate\Foundation\Bus\DispatchesJobs;
use Warksit\LaravelMailChimpSync\Jobs\AddInterestGroup;
use Warksit\LaravelMailChimpSync\Jobs\DeleteInterestGroup;
use Warksit\LaravelMailChimpSync\Models\Interest;

trait MailChimpSyncInterest
{
    use DispatchesJobs;

    public static function bootMailChimpSyncInterest()
    {
        static::saved(function ($model) {
            $model->addInterestGroup();
        });

        static::deleting(function ($model) {
            $model->removeInterestGroup();
        });
    }

    public function interest()
    {
        return $this->morphOne(Interest::class,'interestable');
    }

    protected function addInterestGroup()
    {
        $this->dispatch(new AddInterestGroup($this));
    }

    protected function removeInterestGroup()
    {
        $this->dispatch(new DeleteInterestGroup($this));
    }
   
}