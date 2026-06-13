# Path

The `path` method returns the actual path of an attachment file. Call it without any arguments to retrieve the path of the original file. Pass a style name as an argument to retrieve the path of a specific style.

{% tabs %}
{% tab title="Original file" %}
```php
$model->attachment('file')->path();
```
{% endtab %}

{% tab title="Particular style" %}
```php
$model->attachment('file')->path('cover');
```
{% endtab %}
{% endtabs %}

You can use any generated style name.

```php
$model->attachment('file')->path('thumbnail');
$model->attachment('file')->path('cover');
```

{% hint style="info" %}
Use `path()` when you need the local file path for server-side processing.

Use `url()` when you need a public link to the file.
{% endhint %}
