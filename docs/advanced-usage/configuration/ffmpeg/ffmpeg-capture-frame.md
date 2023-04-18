---
description: 'Default: null'
---

# FFMpeg Capture Frame

This property allows you to specify the time in seconds at which a frame should be captured from a video file during the upload process. You can set the capture frame value as `null`, `0.1`, `2`, or any other desired value in seconds.&#x20;

{% hint style="info" %}
If the capture frame value is set to null, Larupload will automatically capture a frame from the center of the video file.&#x20;
{% endhint %}



{% code title="Example 1:" %}
```php
<?php

return [
    'ffmpeg' => [
        'capture-frame' => null
    ]
];
```
{% endcode %}

{% code title="Example 2:" %}
```php
<?php

return [
    'ffmpeg' => [
        'capture-frame' => 0.1
    ]
];
```
{% endcode %}

{% code title="Example 3:" %}
```php
<?php

return [
    'ffmpeg' => [
        'capture-frame' => 2
    ]
];
```
{% endcode %}

\
\
