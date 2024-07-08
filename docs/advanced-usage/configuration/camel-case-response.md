---
description: 'Default: false'
---

# Camel Case Response

By default, Larupload returns all meta keys in the `snake_case` style. Enabling this option will return them in `camelCase`.

{% code title="Output:" %}
```json
{
    "name": "image.jpg",
    "id": "1",
    "originalName": "image.jpg"
    "size": 35700,
    "type": "IMAGE",
    "mimeType": "image/jpeg",
    "width": 1077,
    "height": 791,
    "duration": null,
    "dominantColor": "#f4c00a",
    "format": "jpg",
    "cover": "image.jpg"
  }
```
{% endcode %}

\
