<?php namespace Warksit\LaravelMailChimpSync\EloquentTraits;


use Illuminate\Foundation\Bus\DispatchesJobs;
use Warksit\LaravelMailChimpSync\Models\Interest;
use Warksit\LaravelMailChimpSync\Jobs\AddInterestGroup;
use Warksit\LaravelMailChimpSync\Jobs\DeleteInterestGroup;

trait MailChimpSyncInterest
{
    use DispatchesJobs, CheckIfEnabled;

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
        if ($this->mailingListEnabled())
            return;

        if ( ! $this->shouldSync()) {
            return $this->dispatch(new DeleteInterestGroup($this));
        }

        $this->dispatch(new AddInterestGroup($this));
    }

    protected function removeInterestGroup()
    {
        if ($this->mailingListEnabled())
            return;

        //No shouldSync() check as only deletes if exists

        $this->dispatch(new DeleteInterestGroup($this));
    }

    abstract function shouldSync();

}