# Queue FFMpeg Processes

{% hint style="info" %}
If you are reading this, we assume you know what is [Laravel Queue](https://laravel.com/docs/queues). if not, please read Laravel's documentation first.
{% endhint %}

Larupload supports queuing of FFMPEG tasks to handle time-consuming operations like cropping, resizing, and streaming of media files.

To enable this feature, you can use the `larupload.ffmpeg.queue` configuration key. Once enabled, you can upload the original file, and Larupload will start processing the video styles in the background, allowing your application to continue functioning smoothly while the file is being processed.

However, there is a limitation for `maximum-queue-num` which is optional. if the maximum number of available queues is exceeded, Larupload will redirect back to the previous URL with a message indicating that the queue limit has been reached. This feature helps you to efficiently handle media files and avoid any delays or timeouts in your application.

