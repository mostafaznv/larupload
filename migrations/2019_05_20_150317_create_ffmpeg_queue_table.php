<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFfmpegQueueTable extends Migration
{
    public function up()
    {
        Schema::create('larupload_ffmpeg_queue', function(Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('record_id');
            $table->string('record_class', 50);
            $table->boolean('status')->default(0);
            $table->text('message')->nullable();

            $table->timestamp('created_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('larupload_ffmpeg_queue');
    }
}
