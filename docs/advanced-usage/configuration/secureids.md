---
description: 'Default: NONE'
---

# SecureIds

This option allows you to hide the real ID of model records by using a different ID format in the file upload path. This can be useful for security or privacy reasons, as it prevents the real IDs from being easily discoverable.

The following ID formats are supported:

* ULID
* UUID
* HASHID
* NONE (use real IDs)

{% hint style="info" %}
To use HASHID method, you must install the [hashids](https://github.com/vinkla/hashids) package
{% endhint %}

{% hint style="warning" %}
SecureIds is disabled in standalone mode
{% endhint %}



