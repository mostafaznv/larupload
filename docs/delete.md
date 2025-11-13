# Delete

There are two ways to delete files using Larupload:

* Delete by `detach` function
* Delete by assigning `false` to file property

{% tabs %}
{% tab title="Detach" %}
```php
$upload->file->detach();
# or (recommended)
$upload->attachment('file')->detach();

$upload->save();
```
{% endtab %}

{% tab title="false" %}
```
$upload->file = false;
$upload->save();
```
{% endtab %}
{% endtabs %}
