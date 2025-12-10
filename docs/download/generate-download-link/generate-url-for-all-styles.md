# Generate URL for all styles

Generating URLs for all styles of an attachment can be done using the `urls` method provided by the Larupload. This method returns an `object` of URLs for all available styles of the attachment.

The `urls` method can be called on an attachment instance, which can be retrieved from a model instance using the `attachment` method. For example, if you have a `User` model with an `avatar` attachment, you can generate URLs for all styles of the `avatar` attachment using the following code:

```php
$user->attachment('avatar')->urls();
```

The returned object contains URLs for all styles of the attachment, including the original file. By default, the properties of the object are the style names, and the values are the corresponding URLs. For example:

{% code title="Output" %}
```json
{
    "original": "https://larupload.dev/storage/users/1/avatar/original/image.jpg",
    "thumbnail": "https://larupload.dev/storage/users/1/avatar/thumbnail/image.jpg",
    "cover": "https://larupload.dev/storage/users/1/avatar/cover/image.jpg"
}
```
{% endcode %}

<br>
