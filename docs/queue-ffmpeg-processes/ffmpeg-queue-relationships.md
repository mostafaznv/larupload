# FFMpeg Queue Relationships

Larupload has two relationships that allow you to view the current status of FFMpeg queue processes and the history of all processes.&#x20;

The `laruploadQueue` relationship provides information about the currently running FFMpeg job. The `laruploadQueues` relationship provides a list of all FFMpeg jobs, including their status, start and end times and any errors that occurred during processing. These relationships can be accessed through the all eloquent models which are using Larupload and provide a simple way to monitor the status of FFMpeg jobs in your application.

```php
Upload::query()
    ->where('id', 21)
    ->with('laruploadQueue', 'laruploadQueues')
    ->first();
```

<br>
