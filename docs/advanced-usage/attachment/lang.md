# Lang

The `lang` function is used to set the language used for generating file names when using the slug template. By default, if the `lang` parameter is not set in the `withMeta` method, the value of the `lang` key in the `config/larupload.php` configuration file will be used. However, you can set a specific language for each attachment instance by passing a string value to the `lang` method of the Attachment instance.

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
            Attachment::make('file')->lang('fa')
        ];
    }
}
```





