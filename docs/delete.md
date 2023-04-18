# Delete

There are two ways to delete files using Larupload:

* Delete by `detach` function
* Delete by assigning `LARUPLOAD_NULL` to file property

{% tabs %}
{% tab title="Detach" %}
```php
$upload->file->detach();
# or (recommended)
$upload->attachment('file')->detach();

$upload->save();
```
{% endtab %}

{% tab title="LARUPLOAD_NULL" %}
```
$upload->file = LARUPLOAD_NULL;
$upload->save();
```
{% endtab %}
{% endtabs %}
