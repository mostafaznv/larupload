---
description: 'Default: ImageStyle Object'
---

# Cover Style

The `cover-style` field allows you to configure the style of the automatically generated cover image.&#x20;

Here is an example configuration:

{% code title="config/larupload.php" %}
```php
<?php

return [
    'cover-style' => ImageStyle::make(
        name: 'cover',
        width: 500,
        height: 500,
        mode: LaruploadMediaStyle::CROP
    )
];
```
{% endcode %}

This configuration specifies that the cover image should have a width and height of 500 pixels. The `LaruploadMediaStyle::CROP` constant indicates that the image should be center cropped to fit the specified dimensions.



