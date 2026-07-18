# Queue Media Details Extraction

{% hint style="info" %}
If you are reading this, we assume you know what is [Laravel Queue](https://laravel.com/docs/queues). if not, please read Laravel's documentation first.
{% endhint %}

Larupload can extract media details either **synchronously** during the upload request or **asynchronously** using Laravel's queue system.

When media details extraction is queued, Larupload uploads the original file immediately and dispatches a background job to extract metadata such as <mark style="color:red;">duration</mark>, <mark style="color:red;">dimensions</mark>, and other available media information. This reduces the upload request time, especially when working with large audio and video files.

If queueing is disabled, Larupload extracts the media details during the upload request, making them available immediately after the upload completes.

To use queued extraction, ensure that your application's queue worker is running so the dispatched jobs can be processed promptly.



{% hint style="info" %}
This feature only works in ORM mode! Media details for <mark style="color:$danger;">standalone</mark> uploads will be extracted synchronously during the request.
{% endhint %}
