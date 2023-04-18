# Keep Old Files

The `keepOldFiles` function works similarly to the `keep-old-files` feature in the configuration file. When this function is enabled, Larupload will not delete the old files associated with the attachment when you `update` the database record. This can be useful if you want to ensure that old versions of the file are still available even after a new version has been uploaded.

By default, this feature is disabled



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
            Attachment::make('file')->keepOldFiles(true)
        ];
    }
}
```



