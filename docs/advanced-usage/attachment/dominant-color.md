# Dominant Color

The `dominantColor` function on the `Attachment` class is used to enable or disable the feature that extracts the dominant color of an image or video. When this feature is enabled, Larupload will extract the dominant color of the uploaded file and store it in the `meta` table in the database.

To use this function, you need to pass a boolean value as an argument. If you set the value to `true`, the dominant color will be extracted, and if you set it to `false`, the feature will be disabled.

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
            Attachment::make('file')->dominantColor(false)
        ];
    }
}
```



