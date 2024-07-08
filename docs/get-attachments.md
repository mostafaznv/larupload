# Get Attachments

The `getAttachments` function allows you to retrieve all URLs and meta information for the attachments assigned to a model. If no argument is provided, it returns URLs and meta for all attachments. However, you can pass the attachment name as an argument to retrieve the data for a specific attachment.



{% tabs %}
{% tab title="Get All Attachments" %}
```php
$user = User::query()->first();
$attachments = $user->getAttachments();
```

{% code title="Output" %}
```json
{
  "avatar": {
    "original": "https://larupload.dev/storage/users/1/avatar/original/image.jpg",
    "thumbnail": "https://larupload.dev/storage/users/1/avatar/thumbnail/image.jpg",
    "cover": "https://larupload.dev/storage/users/1/avatar/cover/image.jpg",
    "meta": {
       "name": "image.jpg",
       "id": "1",
       "original_name": "image.jpg"
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
  },
  "cv": {
    "original": "https://larupload.dev/storage/users/1/cv/original/cv.pdf",
    "cover": "https://larupload.dev/storage/users/1/cv/cover/cv.jpg",
    "meta": {
       "name": "cv.pdf",
       "id": "1",
       "original_name": "image.jpg"
       "size": 71710,
       "type": "DOCUMENT",
       "mime_type": "application/pdf",
       "width": null,
       "height": null,
       "duration": null,
       "dominant_color": "#f4c00a",
       "format": "pdf",
       "cover": "cv.jpg"
    }
  }
}
```
{% endcode %}
{% endtab %}

{% tab title="Get Particular Attachment" %}
```php
$user = User::query()->first();
$attachment = $user->getAttachments('avatar');
```

{% code title="Output" %}
```json
{
   "original": "https://larupload.dev/storage/users/1/avatar/original/image.jpg",
   "thumbnail": "https://larupload.dev/storage/users/1/avatar/thumbnail/image.jpg",
   "cover": "https://larupload.dev/storage/users/1/avatar/cover/image.jpg",
   "meta": {
       "name": "image.jpg",
       "id": "1",
       "original_name": "image.jpg"
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
{% endtab %}
{% endtabs %}



