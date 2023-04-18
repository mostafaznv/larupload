# Cover Style

The `coverStyle` function allows you to set the style of the automatically generated cover image for a specific attachment.&#x20;



```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Mostafaznv\Larupload\Enums\LaruploadMediaStyle;
use Mostafaznv\Larupload\Storage\Attachment;
use Mostafaznv\Larupload\Traits\Larupload;

class Media extends Model
{
    use Larupload;

    public function attachments(): array
    {
        return [
            Attachment::make('file')
                ->coverStyle('cover', 400, 400, LaruploadMediaStyle::CROP)
        ];
    }
}
```



