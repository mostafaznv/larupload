# Delete Cover

You can delete an uploaded/generated cover using the `detach` method. This method removes the specified cover from the associated file and deletes its file from the storage.

```php
$upload = Upload::findOrFail($id);

$upload->file->cover()->detach();
# or (recommended)
$upload->attachment('file')->cover()->detach();

$upload->save();
```
