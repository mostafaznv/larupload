---
description: 'Default: 0'
---

# FFMpeg Max Queue Number

With the `max-queue-num` option, you can limit the number of Larupload instances that are queued for FFMPEG processing.&#x20;

If the number of running FFMPEG queues exceeds the specified limit, Larupload will throw an exception to prevent excessive resource consumption. This can be useful in cases where the server has limited resources and can't handle too many simultaneous FFMPEG processes.

{% hint style="info" %}
If you want to ignore this feature and queue uploaded files unlimited, simply set the value to 0.
{% endhint %}

\
