# SecureIds Method

The `secureIdsMethod` function allows you to specify the method to be used for generating secure IDs for the file upload path. By default, Larupload uses the `NONE` (use real IDs) format, but you can also choose from `ULID`, `UUID`, `HASHID` and `SQID` formats.

Using a secure ID format can be useful for security or privacy reasons, as it prevents the real IDs of model records from being easily discoverable.&#x20;



{% hint style="info" %}
If you choose the `HASHID` format, you must also install the [`hashids`](https://github.com/vinkla/hashids) package.
{% endhint %}

{% hint style="info" %}
If you choose the `SQID` format, you must also install the [`sqids`](https://github.com/sqids/sqids-php) package.
{% endhint %}

{% hint style="warning" %}
`SecureIds` is disabled in standalone mode.
{% endhint %}

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
            Attachment::make('file')
                ->secureIdsMethod(LaruploadSecureIdsMethod::ULID)
        ];
    }
}
```



