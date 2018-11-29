<?php namespace Warksit\LaravelMailChimpSync;

class MailingList
{
    private $enabled;

    /**
     * MailingList constructor, enabled by default.
     * @param $enabled
     */
    public function __construct($enabled)
    {
        $this->enabled = $enabled;
    }

    public function disable()
    {
        $this->enabled = false;
    }

    public function enable()
    {
        $this->enabled = true;
    }

    /**
     * @return boolean
     */
    public function enabled()
    {
        return $this->enabled;
    }

}