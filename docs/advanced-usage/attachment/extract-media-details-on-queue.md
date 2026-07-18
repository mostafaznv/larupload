# Extract Media Details On Queue

The `extractMediaDetailsOnQueue` method determines whether Larupload should extract media details asynchronously using Laravel's queue system.

When enabled, Larupload dispatches a queued job to extract media details such as <mark style="color:red;">duration</mark>, <mark style="color:red;">dimensions</mark>, and other available metadata after the upload process completes. This helps reduce request time, especially when working with large audio or video files.

When disabled, media details are extracted synchronously during the upload request, ensuring the metadata is immediately available once the upload finishes.

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Mostafaznv\Larupload\Storage\Attachment;
use Mostafaznv\Larupload\Traits\Larupload;

class Media extends Model
{
    use Larupload;

    public function attachments(): array
    {
        return [
            Attachment::make('file')->extractMediaDetailsOnQueue(true)
        ];
    }
}
```



{% hint style="info" %}
Run a Laravel queue worker when this option is enabled. Media details remain unavailable until the job completes.
{% endhint %}

{% hint style="info" %}
This feature only works in ORM mode! Media details for <mark style="color:$danger;">standalone</mark> uploads will be extracted synchronously during the request.
{% endhint %}
