<?php namespace Warksit\LaravelMailChimpSync\Interfaces;


interface CanSyncMailChimpMember
{
    /**
     * Returns the MailChimp List Id. On MailChimp go to your list
     * and select Settings -> List Name and defaults
     * The List ID is displayed there
     * 
     * @return mixed
     */
    public function getMailingListId();

    /**
     * Return the email address you want subscribed
     * @return mixed
     */
    public function getMailingListEmail();

    /**
     * Return the name of the email field (likely 'email')
     * @return mixed
     */
    public function getMailingListEmailField();

    /**
     * Return additional data you want synced to MailChimp.
     * See the MailChimp docs for more details
     *
     * Example
     * return ['merge_fields' => ['FNAME' => $this->first_name, 'LNAME' => $this->last_name]
     *
     * You can set up additional merge fields for the list and send those also
     *
     * @return mixed
     */
    public function getMailingListProfile();

    /**
     * You may give your user the option to not receive the email
     * return false; if you don't have any opt out
     * @return bool
     */
    public function getMailingListOptedOut();

    /**
     * return an array of interest_id of interests MailChimp knows about
     * @return mixed
     */
    public function getMailingListInterests();
}