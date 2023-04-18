# Generate Cover

The `generateCover` method allows you to enable or disable the automatic generation of a cover image from the uploaded image or video.

When this function is set to `true`, Larupload will automatically generate a cover image from the uploaded image or video, which can be useful for displaying a preview of the uploaded media. If set to `false`, no cover image will be generated.

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
            Attachment::make('file')->generate(false)
        ];
    }
}
```



