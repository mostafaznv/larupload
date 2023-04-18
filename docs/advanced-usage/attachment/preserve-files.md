# Preserve Files

The `preserveFiles` function on the `Attachment` is used to enable or disable the preservation of files when `deleting` a record. When this function is enabled, Larupload will not delete the files associated with a record even when the record is deleted from the database. This can be useful in situations where you want to keep the files for some other purpose, even after the associated record has been deleted. By default, `preserveFiles` is set to `false` which means that files will be deleted when the associated record is deleted from the database.

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
            Attachment::make('file')->preserveFiles(true)
        ];
    }
}
```



