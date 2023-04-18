# Naming Method

`namingMethod()` method in the `Attachment` class allows users to choose one of the three available naming methods for file uploads. These methods are:

1. `HASH_FILE`: This method generates a unique name for the uploaded file based on the file's contents using the `md5` algorithm.
2. `TIME`: This method generates a name for the uploaded file based on the current timestamp.
3. `SLUG`: This method generates a name for the uploaded file using a slug representation of the original filename.

Users can set the naming method for an attachment by passing one of these options as a parameter to the `namingMethod()` method. For example:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Mostafaznv\Larupload\Enums\LaruploadNamingMethod;
use Mostafaznv\Larupload\Storage\Attachment;
use Mostafaznv\Larupload\Traits\Larupload;

class Media extends Model
{
    use Larupload;

    public function attachments(): array
    {
        return [
            Attachment::make('file')
                ->namingMethod(LaruploadNamingMethod::HASH_FILE)
        ];
    }
}
```

This will set the naming method for the attachment to the `HASH_FILE` method.



