# Update Cover

Once you've uploaded a file, you can update its cover at any time.

```php
$cover = $request->file('cover');

$upload = Upload::query()->first();

$upload->file->cover()->update($cover);
# or (recommended)
$upload->attachment('file')->cover()->update($cover);

$upload->save();
```



