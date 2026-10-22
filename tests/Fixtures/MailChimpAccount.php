<?php

namespace Warksit\LaravelMailChimpSync\Tests\Fixtures;

use Warksit\LaravelMailChimpSync\MailChimp\MailChimpAuth;

// The account the fixture models sync with: dummy values for the unit suite, which
// never leaves the mocked HTTP client, and the real ones from .env for integration.
class MailChimpAccount
{
    public static $endpoint = 'https://us1.api.mailchimp.com';
    public static $token = 'token';
    public static $listId = 'list1';
    public static $categoryId = 'cat1';

    public static function auth()
    {
        return new MailChimpAuth(static::$endpoint, static::$token);
    }

    public static function reset()
    {
        static::$endpoint = 'https://us1.api.mailchimp.com';
        static::$token = 'token';
        static::$listId = 'list1';
        static::$categoryId = 'cat1';
    }
}
