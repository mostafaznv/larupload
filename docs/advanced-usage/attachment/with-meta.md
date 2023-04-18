# With Meta

The `withMeta` method allows you to include metadata associated with uploaded files in the response of the `urls` method. When this method is enabled for an attachment instance, metadata such as the format of the file, its dimensions, dominant color, and other relevant information will be included in the response along with the URLs.

To enable `withMeta` for an attachment, simply call the method on the attachment instance, like so:

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
            Attachment::make('file')->withMeta(true)
        ];
    }
}
```

This will enable metadata to be included in the response when the `urls` method is called on this attachment instance.



