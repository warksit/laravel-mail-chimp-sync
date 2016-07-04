<?php namespace Warksit\LaravelMailChimpSync\EloquentTraits;


use Illuminate\Foundation\Bus\DispatchesJobs;
use Warksit\MailingList\Jobs\AddInterestGroup;
use Warksit\MailingList\Jobs\DeleteInterestGroup;

trait MailChimpSyncInterest
{
    use DispatchesJobs;

    public static function bootMailingListInterest()
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
        return $this->morphOne(\Warksit\MailingList\Models\Interest::class,'interestable');
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