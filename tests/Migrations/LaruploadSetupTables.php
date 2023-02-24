<?php

namespace Mostafaznv\Larupload\Test\Migrations;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Mostafaznv\Larupload\Enums\LaruploadMode;
use Mostafaznv\Larupload\Larupload;

class LaruploadSetupTables extends Migration
{
    /**
     * Run the migrations
     *
     * @return  void
     */
    public function up(): void
    {
        Schema::create('upload_heavy', function(Blueprint $table) {
            $table->id();
            $table->upload('main_file', LaruploadMode::HEAVY);
            $table->timestamps();
        });

        Schema::create('upload_light', function(Blueprint $table) {
            $table->id();
            $table->upload('main_file', LaruploadMode::LIGHT);
            $table->timestamps();
        });

        Schema::create('upload_soft_delete', function(Blueprint $table) {
            $table->id();
            $table->upload('main_file', LaruploadMode::HEAVY);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create(Larupload::FFMPEG_QUEUE_TABLE, function(Blueprint $table) {
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
    public function down(): void
    {
        Schema::dropIfExists('upload_heavy');
        Schema::dropIfExists('upload_light');
        Schema::dropIfExists('upload_soft_delete');
        Schema::dropIfExists(Larupload::FFMPEG_QUEUE_TABLE);
    }
}
