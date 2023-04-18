# Light Columns

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Mostafaznv\Larupload\Enums\LaruploadMode;

return new class extends Migration {
    public function up()
    {
        Schema::create('uploads', function(Blueprint $table) {
            $table->id();
            $table->upload('file', LaruploadMode::LIGHT);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('uploads');
    }
};
```

The following columns are created in `LIGHT` mode:&#x20;

* `file_name`
* `file_meta`



{% hint style="info" %}
When using **MySQL 5.7.8** or a later version, the `meta` field will be stored as a `JSON` type. This makes it easy to work with the metadata associated with each file.

However, if you are using an older version of MySQL, the `meta` field will be stored as a `TEXT` type instead.&#x20;
{% endhint %}



