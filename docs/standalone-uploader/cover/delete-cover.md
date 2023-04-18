# Delete Cover

To delete the cover file for an attachment in standalone mode, you can use the `deleteCover` method.&#x20;

```php
<?php

namespace App\Http\Controllers;

use Mostafaznv\Larupload\Larupload;
use Illuminate\Http\Request;

class UploadController extends Controller
{
    public function store(): RedirectResponse
    {        
        Larupload::init('uploaded/base/path')->deleteCover();

        return redirect()->back();
    }
}
```



