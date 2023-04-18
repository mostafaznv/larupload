# Job Completion Event

Larupload provides an event that fires when an FFMpeg job finishes processing in the queue. This event can be used to perform additional actions once the job is complete, such as sending a notification to a user or updating a database record. The event can be listened to using Laravel's built-in event system, and it provides access to the attachment model and the original request.

To utilize this feature, you need to create an event listener and register it. Once the job is completed, Larupload will notify your listener and provide the necessary information.



1. **Create Listener**

```bash
php artisan make:listener LaruploadFFMpegQueueNotification
```

2. **Register Listener**

{% code title="App\Providers\EventServiceProvider" %}
```php
use App\Events\OrderShipped;
use Mostafaznv\Larupload\Events\LaruploadFFMpegQueueFinished;
use App\Listeners\LaruploadFFMpegQueueNotification;
 

protected $listen = [
    LaruploadFFMpegQueueFinished::class => [
        LaruploadFFMpegQueueNotification::class,
    ],
];
```
{% endcode %}

3. **Fetch Notification**

```php
<?php

namespace App\Listeners;

class LaruploadFFMpegQueueNotification
{
    public function handle(LaruploadFFMpegQueueFinished $event)
    {
        info("larupload queue finished. id: $event->id, model: $event->model, statusId: $event->statusId");
    }
}
```
