<?php namespace Warksit\LaravelMailChimpSync\EloquentTraits;

use Warksit\LaravelMailChimpSync\MailingList;

trait CheckIfEnabled
{
    /**
     * @return bool
     */
    private function mailingListEnabled()
    {
        return !resolve(MailingList::class)->enabled();
    }
}