# API Resources

Larupload automatically includes all attachments in JSON responses, but if you want to retrieve the attachments manually, you can use the built-in `toArray()` and `toJson()` methods, which will return an array or JSON representation of the model, including all attachments.

```php
$upload = Upload::query()->first();

$upload->toArray();
$upload->toJson();
```



