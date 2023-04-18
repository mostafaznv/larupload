---
description: 'Default: false'
---

# FFMpeg Queue

Enabling this option allows you to queue the FFMpeg process and perform the video conversion in the background. This is particularly useful if you have a large number of videos to process or if the FFMpeg process is very heavy and might cause performance issues.&#x20;

By default, this option is set to `false`, which means that the FFMPEG process will be executed synchronously. To enable the queue, you can set this option to `true`, for example:

{% code title="config/larupload.php" %}
```php
<?php

return [
    'ffmpeg' => [
        'queue' => true
    ]
];
```
{% endcode %}

This will queue the FFMpeg process and perform the video conversion in the background. You will need to make sure that you have set up a queue worker to process the queued jobs.



