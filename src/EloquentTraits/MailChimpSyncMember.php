<?php namespace Warksit\LaravelMailChimpSync\EloquentTraits;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Warksit\LaravelMailChimpSync\Models\MailingList;
use Warksit\LaravelMailChimpSync\Jobs\SubscribeToMailingList;
use Warksit\LaravelMailChimpSync\Jobs\UnSubscribeFromMailingList;

trait MailChimpSyncMember
{
    use DispatchesJobs, CheckIfEnabled;

    private $mailingListOldEmail = null;

    protected static function bootMailChimpSyncMember()
    {
        static::saving(function ($model) {
            $model->mailingListPreSave();
        });
        static::saved(function ($model) {
            $model->mailingListPostSave();
        });
        static::deleting(function ($model) {
            $model->mailingListDeleting();
        });
    }

    protected function mailingListPreSave()
    {
        if ($this->mailingListEnabled())
            return;

        if ($this->exists && array_key_exists($this->getMailingListEmailField() , $this->getDirty()))
            $this->mailingListOldEmail = $this->getOriginal($this->getMailingListEmailField());
    }

    protected function mailingListPostSave()
    {
        if ($this->mailingListEnabled())
            return;

        if($this->mailingListOldEmail)
            $this->mailingListUnSubscribe($this->mailingListOldEmail);

        $this->mailingListSubscribe();
    }

    protected function mailingListDeleting()
    {
        if ($this->mailingListEnabled())
            return;

        $this->mailingListUnSubscribe($this->getMailingListEmail());
    }

    public function mailingList()
    {
        return $this->morphOne(MailingList::class,'mailable');
    }

    public function mailingListUnSubscribe($email)
    {
        $this->dispatch(new UnSubscribeFromMailingList($this, $email));
    }

    public function mailingListSubscribe()
    {
        $this->dispatch(new SubscribeToMailingList($this));
    }


}