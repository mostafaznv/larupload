# Heavy Columns

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
            $table->upload('file', LaruploadMode::HEAVY);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('uploads');
    }
};
```

The following columns are created in `HEAVY` mode:&#x20;

* `file_name`
* `file_id`
* `file_size`
* `file_type`
* `file_mime_type`
* `file_width`
* `file_height`
* `file_duration`
* `file_dominant_color`
* `file_format`
* `file_cover`

These columns store separate information about the file and are useful when you need to perform special queries or sort data.



{% hint style="info" %}
All fields created in `HEAVY` mode are nullable.
{% endhint %}

{% hint style="info" %}
`file_size`, `file_type`, and `file_duration` columns have an index.
{% endhint %}



