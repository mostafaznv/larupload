---
description: 'Default: false'
---

# Extract Media Details On Queue

Set <mark style="color:red;">`extract-media-details-on-queue`</mark> to <mark style="color:red;">`true`</mark> to extract media details asynchronously.

Larupload dispatches a queue job for details such as duration and resolution. This keeps the upload request responsive.

Set it to `false` to extract details synchronously during the upload request.

{% hint style="info" %}
Run a Laravel queue worker when this option is enabled. Media details remain unavailable until the job completes.
{% endhint %}

{% hint style="info" %}
This feature only works in ORM mode! Media details for <mark style="color:$danger;">standalone</mark> uploads will be extracted synchronously during the request.
{% endhint %}
