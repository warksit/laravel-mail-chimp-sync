# Laravel MailChimp Sync for Laravel 5.2

A package to sync subscribers and interests (using eloquent events) with MailChimp (API v3) for Laravel 5.2+. It's only dependency is Guzzle 6.

## Installation

To get started, require this package:

```bash
composer require warksit/laravel-mail-chimp-sync
```

Add the ServiceProvider  in `config/app.php`:

```php
    'providers' => [
        ...
        Warksit\LaravelMailChimpSync\MailingListServiceProvider::class,
    ],
```

Next run vendor:publish to copy the migrations.

```bash
php artisan vendor:publish --provider="Warksit\LaravelMailChimpSync\MailingListServiceProvider"
```

Run the migrations:

```bash
php artisan migrate

```

## Usage

This package has 2 main uses. It syncs subscribers to a list(s) and syncs a list of interests of an interest group(s). It makes use of eloquent events to detect changes and also makes use of queued jobs so the relative expensive calls to MailChimp don't slow your program flow. The one exception is deleting a model as we need to remove that from MailChimp while the record still exists.

### Sync Subscribers

This allows you to keep changes you make to an Eloquent Model in sync with a MailChimp List. It uses eloquent events. This is achieved through the ```Warksit\LaravelMailChimpSync\EloquentTraits\MailChimpSyncMember``` trait. This requires a few methods on the model which are enforced by implementing the ```Warksit\LaravelMailChimpSync\EloquentTraits\CanSyncMailChimpMember``` interface. Have a look in the doc blocks for more details. If you have overidden the ```boot()``` method make sure you call ```parent::boot()``` no worries if you haven't.

```php
    use Warksit\LaravelMailChimpSync\Interfaces\CanSyncMailChimpMember;
    use Warksit\LaravelMailChimpSync\EloquentTraits\MailChimpSyncMember;
    
    class YourModel extends Model implements CanSyncMailChimpMember
    {
        use MailChimpSyncMember
        
        protected static function boot()
        {
            parent::boot();
            ...
        }
    }
```

### Sync Interest

This allows you to keep changes you make to an Eloquent Model in Sync as the Interest for an Interest Group. This is achieved through the ```Warksit\LaravelMailChimpSync\EloquentTraits\MailChimpSyncInterest``` trait. This requires a few methods on the model which are enforced by implementing the ```Warksit\LaravelMailChimpSync\EloquentTraits\CanSyncMailChimpInterest``` interface. Have a look in the doc blocks for more details. Again if you have overidden the ```boot()``` method make sure you call ```parent::boot()``` no worries if you haven't.
```php

    use Warksit\LaravelMailChimpSync\Interfaces\CanSyncMailChimpInterest;
    use Warksit\LaravelMailChimpSync\EloquentTraits\MailChimpSyncInterest;
    
    class YourInterestModel extends Model implements CanSyncMailChimpInterest
    {
        use MailChimpSyncInterest;
        
        protected static function boot()
        {
            parent::boot();
            ...
        }
    }
```

### Manual Sync

If you would like to trigger a sync for a model at another time just call ```$memberModel->mailingListSubscribe()``` to resync a member or ```$interestModel->addInterestGroup()``` on the relevant model. This is particularly useful to add a new interest to a member.

## Contributing

Please! This meets my needs but am open to help. If you find an issue, or have a better way to do something, open an issue or a pull request. There are some tests. To get the integration tests to work copy ```.env.example``` to ```.env``` and enter your MailChimp credentials.