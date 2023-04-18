# Optimize Image

The `optimizeImage` function allows you to enable or disable the image optimization feature for uploaded images. When enabled, the uploaded image will be optimized using the `spatie/image-optimizer` package, which can reduce the file size of the image without affecting its quality.

By default, image optimization is disabled in Larupload. You can enable it by calling the `optimizeImage` function and passing `true` as the argument, like this:



```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Mostafaznv\Larupload\Enums\LaruploadSecureIdsMethod;
use Mostafaznv\Larupload\Storage\Attachment;
use Mostafaznv\Larupload\Traits\Larupload;

class Media extends Model
{
    use Larupload;

    public function attachments(): array
    {
        return [
            Attachment::make('file')->optimizeImage(ture)
        ];
    }
}
```



