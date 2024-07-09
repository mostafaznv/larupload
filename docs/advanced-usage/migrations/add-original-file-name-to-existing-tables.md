# Add Original File Name to Existing Tables

As the <mark style="color:red;">{$name}\_file\_original\_name</mark> column has been added to Larupload since <mark style="color:red;">v2.2.0</mark>, you may need to add this specific column to your <mark style="color:red;">**existing**</mark> tables.



```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->laruploadAddOriginalName('file');
        });
    }
};
```

{% hint style="info" %}
The first argument of `laruploadAddOriginalName` should match the first argument of `upload`.
{% endhint %}

{% hint style="info" %}
This column is only used when you've created table columns in HEAVY mode before.
{% endhint %}



