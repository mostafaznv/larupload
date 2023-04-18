# Database Preparation

To use Larupload, you must first add the corresponding columns to the desired table in your database. You can achieve this by using the `upload macro` provided by Larupload in your migration file. This macro will create the required columns for Larupload to function properly.



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
            $table->upload('main_file', LaruploadMode::HEAVY);
            $table->upload('other_file', LaruploadMode::LIGHT);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('uploads');
    }
};
```



