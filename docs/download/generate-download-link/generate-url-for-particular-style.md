# Generate URL for particular style

The `url` method in Larupload is a convenient way to generate URLs for accessing uploaded files. You can use it to generate URLs for a specific style of an attachment or for the original file.

By default, if you don't pass any arguments to the `url` method, it returns the URL of the original style of the attachment. However, if you want to generate a URL for a specific style, you can pass the style name as an argument to the `url` method.

For example, let's say you have an attachment called `avatar` and you want to generate a URL for its `thumb` style. You can do this by calling the `url` method on the `avatar` attachment and passing the `thumb` style as an argument, like this:

```php
$model->attachment('avatar')->url('thumb');
```

This will generate a URL that points to the `thumb` style of the `avatar` attachment.

And if you want to get the original file of the `avatar` attachment, you can generate it like this:

```php
$model->attachment('avatar')->url();
# or
$model->attachment('avatar')->url('original');
```



