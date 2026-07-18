# Media Details Extraction Queue Relationships

Larupload provides two relationships that allow you to monitor media details extraction queue processes and view the history of all extraction jobs.

The <mark style="color:red;">`laruploadMediaDetailsQueue`</mark> relationship provides information about the currently running media details extraction job. The <mark style="color:red;">`laruploadMediaDetailsQueues`</mark> relationship provides a list of all media details extraction jobs, including their status, start and end times, and any errors that occurred during processing.

These relationships are available on all Eloquent models using Larupload and provide a simple way to monitor media details extraction processes in your application.

```php
Upload::query()
    ->where('id', 21)
    ->with('laruploadMediaDetailsQueue', 'laruploadMediaDetailsQueues')
    ->first();
```

<br>
