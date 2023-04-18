# Update Cover

To update the cover file for an attachment in standalone mode, you can use the `changeCover` method.&#x20;

This method takes an argument which is the new cover file.&#x20;

```php
<?php

namespace App\Http\Controllers;

use Mostafaznv\Larupload\Larupload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UploadController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $cover = $request->file('cover');
        
        Larupload::init('uploaded/base/path')->changeCover($cover);

        return redirect()->back();
    }
}
```

