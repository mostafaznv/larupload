# Job Completion Event

Larupload dispatches the <mark style="color:red;">`LaruploadMediaDetailsQueueFinished`</mark> event after a queued media details extraction job has completed successfully.

This event is useful when you need to perform follow-up actions once media metadata has been extracted, such as:

* Updating related records
* Dispatching additional jobs
* Sending notifications
* Synchronizing data with external services

#### Event Payload

The event exposes the following properties:

| Property | Type   |
| -------- | ------ |
| id       | int    |
| model    | string |
| statusId | int    |





1. **Create Listener**

```bash
php artisan make:listener LaruploadMediaDetailsQueueFinishedListener
```

2. **Register Listener**

Laravel will automatically discover and register the listener as long as it is placed in your application's `Listeners` directory and the event is type-hinted in the `handle` method.

If event discovery is disabled, you can register the listener manually using Laravel's event registration mechanism.

{% code title="App\Providers\EventServiceProvider" %}
```php
use App\Events\OrderShipped;
use Mostafaznv\Larupload\Events\LaruploadMediaDetailsQueueFinished;
use App\Listeners\LaruploadMediaDetailsQueueFinishedListener;
 

protected $listen = [
    LaruploadMediaDetailsQueueFinished::class => [
        LaruploadMediaDetailsQueueFinishedListener::class,
    ],
];
```
{% endcode %}

3. **Listen**

```php
<?php

namespace App\Listeners;

class LaruploadMediaDetailsQueueFinishedListener
{
    public function handle(LaruploadMediaDetailsQueueFinished $event)
    {
        info("larupload media details extraction finished. id: $event->id, model: $event->model, statusId: $event->statusId");
    }
}
```
