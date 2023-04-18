# Upload

Uploading files is easy with Larupload. you can upload just like you do with other fields in your model. Larupload will automatically upload the file and store the information in the database.



```php
<?php

namespace App\Http\Controllers;

use App\Models\Upload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UploadController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $upload = new Upload;
        $upload->main_file = $request->file('file');
        $upload->save();

        return redirect()->back();
    }
}
```

