<?php

namespace Mostafaznv\Larupload\Test\Migrations;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Mostafaznv\Larupload\LaruploadEnum;

class LaruploadSetupTables extends Migration
{
    /**
     * Run the migrations
     *
     * @return  void
     */
    public function up()
    {
        Schema::create('upload_heavy', function(Blueprint $table) {
            $table->id();
            $table->upload('main_file', LaruploadEnum::HEAVY_MODE);
            $table->timestamps();
        });

        Schema::create('upload_light', function(Blueprint $table) {
            $table->id();
            $table->upload('main_file', LaruploadEnum::LIGHT_MODE);
            $table->timestamps();
        });

        Schema::create('upload_soft_delete', function(Blueprint $table) {
            $table->id();
            $table->upload('main_file', LaruploadEnum::HEAVY_MODE);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create(LaruploadEnum::FFMPEG_QUEUE_TABLE, function(Blueprint $table) {
            $table->id();
            $table->unsignedInteger('record_id');
            $table->string('record_class', 50);
            $table->boolean('status')->default(0);
            $table->text('message')->nullable();

            $table->timestamp('created_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return  void
     */
    public function down()
    {
        Schema::dropIfExists('upload_heavy');
        Schema::dropIfExists('upload_light');
        Schema::dropIfExists('upload_soft_delete');
        Schema::dropIfExists(LaruploadEnum::FFMPEG_QUEUE_TABLE);
    }
}
