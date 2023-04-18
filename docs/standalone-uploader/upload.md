# Upload

In standalone mode, you can use the `upload` method to upload a file. This method takes in the the original file and the cover which is optional. Once the upload is complete, Larupload will automatically generate a unique filename and save the file to the specified path.

```php
<?php

namespace App\Http\Controllers;

use Mostafaznv\Larupload\Larupload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UploadController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $file = $request->file('file');
        $cover = $request->file('cover');
        
        $upload = Larupload::init('your/base/path')->upload($file, $cover);

        return response()->json($upload);
    }
}
```

{% code title="Output" %}
```
{
    "original": "http://larupload.site/storage/uploader/original/a3ac7ddabb263c2d00b73e8177d15c8d.mp4",
    "meta": {
        "name": "a3ac7ddabb263c2d00b73e8177d15c8d.mp4"
        "id": "125940123"
        "size": 383631
        "type": "video"
        "width": 560
        "height": 320
        "duration": 5
        "format": "mp4"
        "cover": "66ad2a5ebfe7ea349c8b861399c060d8.jpeg"
        "mime_type": "video/mp4"
        "dominant_color": "#e5d2d4"
    }
}
```
{% endcode %}

