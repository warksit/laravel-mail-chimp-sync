<?php namespace Warksit\LaravelMailChimpSync\Interfaces;


interface CanSyncMailChimpInterest
{
    /**
     * The list this Interest relates to
     * @return mixed
     */
    public function getInterestListId();

    /**
     * The Interest Category Id this interest syncs with
     * @return mixed
     */
    public function getInterestCategoryId();

    /**
     * You can specify an integer to define the order they are displayed
     * These appear to have to be consecutive. May be easier to re-order
     * on MailChimp website
     *
     * @return mixed
     */
    public function getInterestDisplayOrder();

    /**
     * The name you would like for the Interest
     * @return mixed
     */
    public function getInterestName();
}