<?php namespace Warksit\LaravelMailChimpSync\Objects;

class MailChimp
{
    public $id;
    public $unique_email_id;

    /**
     * MailChimp constructor.
     * @param $value
     */
    public function __construct($value)
    {
        $object = json_decode($value);
        $this->id = $object->id;
        $this->unique_email_id = $object->unique_email_id;
    }

}