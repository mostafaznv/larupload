# Image Processing Library

The `imageProcessingLibrary` method is used to set the image processing library for a specific attachment. It accepts a string parameter that can be either `GD` or `IMAGICK` to specify the library to use for image manipulation operations like cropping and resizing.

By default, Larupload uses the GD library for image processing, but you can override this at the attachment level by calling the `imageProcessingLibrary` method on the `Attachment` instance. This allows you to have different attachments that use different image processing libraries, depending on your needs.

Here is an example of how to use the `imageProcessingLibrary` method to set the image processing library for an attachment:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Mostafaznv\Larupload\Enums\LaruploadImageLibrary;
use Mostafaznv\Larupload\Storage\Attachment;
use Mostafaznv\Larupload\Traits\Larupload;

class Media extends Model
{
    use Larupload;

    public function attachments(): array
    {
        return [
            Attachment::make('file')
                ->imageProcessingLibrary(LaruploadImageLibrary::GD)
        ];
    }
}
```



