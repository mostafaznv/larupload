# Meta

The `meta` method in Larupload is used to retrieve meta information about an attachment. This method can provide various types of information, such as the `size`, `type`, `mimeType`, `duration`, `width`, `height`, and `dominantColor` of the uploaded file. The method takes an optional argument that specifies which specific piece of meta information is to be retrieved. If no argument is provided, the method returns all available meta information for the attachment.

For example, to retrieve the size of an attachment named "file", you can use the following code:

```php
$size = $model->attachment('file')->meta('size');
```

Similarly, to retrieve the MIME type of the same attachment, you can use the following code:

```php
$mime = $model->attachment('file')->meta('mime_type');
```

If you want to retrieve all available meta information for the attachment, you can simply call the `meta` method without any arguments, like this:

```php
$meta = $model->attachment('file')->meta();
```

{% code title="Output" %}
```json
{
    "name": "9e55cf595703eaa109025073caed65a4.jpg",
    "id": "1",
    "size": 35700,
    "type": "IMAGE",
    "mime_type": "image/jpeg",
    "width": 1077,
    "height": 791,
    "duration": null,
    "dominant_color": "#f4c00a",
    "format": "jpg",
    "cover": "9e55cf595703eaa109025073caed65a4.jpg"
  }
```
{% endcode %}



{% hint style="info" %}
It's important to note that the `type` field returned by the `meta` object is based on the file extension, and not on the actual contents of the file.



Here are some more details about each type:

* IMAGE: for files with extensions like jpg, png, gif, bmp, etc.
* VIDEO: for files with extensions like mp4, avi, mov, wmv, etc.
* AUDIO: for files with extensions like mp3, wav, ogg, etc.
* DOCUMENT: for files with extensions like doc, docx, pdf, ppt, pptx, xls, xlsx, etc.
* COMPRESSED: for files with extensions like zip, rar, 7z, etc.
* FILE: for all other file types that don't fall into the above categories.
{% endhint %}



