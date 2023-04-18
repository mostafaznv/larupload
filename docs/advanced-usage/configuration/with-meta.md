---
description: 'Default: true'
---

# With Meta

By enabling this property, the metadata associated with uploaded files - such as their format, dimensions, dominant color and etc - will be included in the response of `urls` function.



{% code title="Example:" %}
```php
$user->attachment('avatar')->urls();
```
{% endcode %}

{% code title="Output:" %}
```json
{
    "original": "https://larupload.dev/storage/users/1/avatar/original/image.jpg",
    "thumbnail": "https://larupload.dev/storage/users/1/avatar/thumbnail/image.jpg",
    "cover": "https://larupload.dev/storage/users/1/avatar/cover/image.jpg",
    "meta": {
        "name": "image.jpg",
        "id": "1",
        "size": 35700,
        "type": "IMAGE",
        "mime_type": "image/jpeg",
        "width": 1077,
        "height": 791,
        "duration": null,
        "dominant_color": "#f4c00a",
        "format": "jpg",
        "cover": "image.jpg"
  }
}
```
{% endcode %}

\
