# Upload Cover

In Larupload, covers are associated with the original files and must be uploaded using the `attach()` function. When uploading a file, you can also include a cover as the second argument. If a cover is provided, it will be assigned to the uploaded file and the [automatic cover creation](#user-content-fn-1)[^1] by the package will be prevented.

```php
$file = $request->file('file');
$cover = $request->file('cover');
# or
$upload->attachment('file')->attach($file, $cover);

$upload->save();
```





[^1]: it's only available for image and videos
