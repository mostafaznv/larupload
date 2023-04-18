# Delete

In standalone mode, you can use the `delete` method to delete an attachment. To delete an attachment, you need to provide the base path of the attachment you want to delete.

Here's an example of how to use the `delete` method in standalone mode:

```php
Larupload::init('your/base/path')->delete();
```

{% hint style="info" %}
When you delete an attachment using the `delete` method, all of its associated styles and covers will be automatically deleted as well.
{% endhint %}



