---
description: 'Default: System Environment'
---

# FFMpeg Binaries

The `ffmpeg-binaries` configuration option allows you to specify the path to the `ffmpeg` and `ffprobe` binaries if they are not in the system environment path.&#x20;



Example:

{% code title="config/larupload.php" %}
```php
<?php

return [
    'ffmpeg' => [
        'ffmpeg.binaries'  => '/usr/local/bin/ffmpeg',
        'ffprobe.binaries' => '/usr/local/bin/ffprobe'
    ]
];
```
{% endcode %}

{% hint style="info" %}
The `ffmpeg` binary is used for video and audio processing, while the `ffprobe` binary is used for metadata extraction.
{% endhint %}



