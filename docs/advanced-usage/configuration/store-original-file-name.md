---
description: 'Default: false'
---

# Store Original File Name

<mark style="color:red;">\[Deprecated]</mark> Enabling this option allows you to store the original file name of the uploaded file in the database. Since files may be stored with custom file names based on your preferred <mark style="color:red;">naming method</mark>, storing the original file name in the database can be beneficial for displaying it in your application's UI or elsewhere.





{% hint style="info" %}
This feature has been available since version <mark style="color:red;">2.2.0</mark>
{% endhint %}

{% hint style="warning" %}
This feature has been <mark style="color:red;">deprecated</mark> since version <mark style="color:red;">3.0.0</mark> and storing original file names is now enabled by default.
{% endhint %}

{% hint style="warning" %}
By enabling this property, all your uploading processes will store the original file name in the database. Therefore, you need to add a<mark style="color:red;">`{$name}_file_original_name`</mark> column to all relevant tables.

For new tables, this will be handled by default. However, if you want to use this feature with existing tables, you must create a [new migration](../migrations/add-original-file-name-to-existing-tables.md) file and add the column to those tables.
{% endhint %}



