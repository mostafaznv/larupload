# Disk

The `disk` method is used to set the disk name that the attachment will be stored on. By default, attachments are stored on the disk specified in the configuration file, but you can use the `disk` method to override this and store an attachment on a different disk.

If you want to set the disk name for an attachment, you can pass the name of the disk as an argument to the `disk` method. For example:

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
            Attachment::make('file')->disk('s3')
        ];
    }
}
```

Once you've set the disk name for an attachment, any subsequent operations on the attachment (such as uploading, updating, or deleting) will be performed on the specified disk.&#x20;

{% hint style="info" %}
The disk must be defined in the `config/filesystems.php` file for Larupload to be able to use it.
{% endhint %}



