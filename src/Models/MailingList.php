<?php namespace Warksit\LaravelMailChimpSync\Models;

use Illuminate\Database\Eloquent\Model;

class MailingList extends Model
{
    protected $fillable=['data'];
    
    public function mailable()
    {
        return $this->morphTo();
    }
}