# Process Finished Event

Larupload dispatches this event after an upload process has been completed successfully. The event is fired in both ORM and standalone modes.

Use this event to execute follow-up actions, such as:

* Sending notifications
* Dispatching background jobs
* Synchronizing data with external services
* Updating related records



### Event Payload

The event exposes the following properties:

| Property | Type   |
| -------- | ------ |
| id       | int    |
| model    | string |

### Create a Listener

```sh
php artisan make:listener LaruploadProcessFinishedListener
```

### Handle the Event

```php
<?php

namespace App\Listeners;

use Vendor\Package\Events\LaruploadProcessFinished;


class LaruploadProcessFinishedListener
{
    public function handle(LaruploadProcessFinished $event): void
    {
        info('Larupload process finished', [
            'id' => $event->id,
            'model' => $event->model,
        ]);
    }
}
```

Laravel will automatically discover and register the listener as long as it is placed in your application's <mark style="color:red;">`Listeners`</mark> directory and the event is type-hinted in the <mark style="color:red;">`handle`</mark> method.

### Manual Registration (Optional)

If you have disabled event discovery, you may register the listener manually in your <mark style="color:red;">`AppServiceProvider`</mark>:

```
<?php

namespace App\Providers;

use App\Listeners\LaruploadProcessFinishedListener;
use Illuminate\Support\Facades\Event;
use Mostafaznv\Larupload\Events\LaruploadProcessFinished;


class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Event::listen(
            LaruploadProcessFinished::class,
            LaruploadProcessFinishedListener::class
        );
    }
}
```

***
