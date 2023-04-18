# Dominant Color Quality

The `dominantColorQuality` function allows you to adjust the quality settings for calculating the dominant color of an image or video. The quality settings range from 1 to 10, with 1 being the highest quality and 10 being the default. Increasing the quality setting will result in more accurate color extraction, but may also consume more memory and processing power. If the quality settings are set too high relative to the size of the image, it may exceed the memory limit set in the PHP configuration, resulting in slow computation.

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
            Attachment::make('file')->dominantColorQuality(6)
        ];
    }
}
```



