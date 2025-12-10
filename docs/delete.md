# Delete

There are two ways to delete files using Larupload:

* Delete by `detach` function
* Delete by assigning `false` to the file property
* Delete by assigning `LARUPLOAD_NULL` to the file property <mark style="color:red;">\[deprecated]</mark>



{% tabs %}
{% tab title="Detach" %}
```php
$upload->file->detach();
# or (recommended)
$upload->attachment('file')->detach();

$upload->save();
```
{% endtab %}

{% tab title="False" %}
```
$upload->file = LARUPLOAD_NULL;
$upload->save();
```



This feature was <mark style="color:red;">introduced</mark> in version <mark style="color:red;">3.0.0</mark>.
{% endtab %}

{% tab title="LARUPLOAD_NULL" %}
```
$upload->file = LARUPLOAD_NULL;
$upload->save();
```



This feature has been <mark style="color:red;">deprecated</mark> since version <mark style="color:red;">3.0.0</mark>. We recommend using the `detach` method or assigning `false` instead.
{% endtab %}
{% endtabs %}
