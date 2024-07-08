<?php

use Illuminate\Database\Schema\Blueprint;
use Mostafaznv\Larupload\Enums\LaruploadFileType;
use Mostafaznv\Larupload\Enums\LaruploadMode;


it('will create all columns in heavy mode', function() {
    $columns = macroColumns(LaruploadMode::HEAVY);

    expect($columns)
        ->toBeArray()
        ->toHaveCount(11)
        //
        ->and($columns)
        ->toHaveKey('file_file_name')
        ->and($columns['file_file_name'])
        ->toBeArray()
        ->toHaveKey('type', 'string')
        ->toHaveKey('length', 255)
        ->toHaveKey('nullable', true)
        //
        ->and($columns)
        ->toHaveKey('file_file_id')
        ->and($columns['file_file_id'])
        ->toBeArray()
        ->toHaveKey('type', 'string')
        ->toHaveKey('length', 36)
        ->toHaveKey('nullable', true)
        //
        ->and($columns)
        ->toHaveKey('file_file_size')
        ->and($columns['file_file_size'])
        ->toBeArray()
        ->toHaveKey('type', 'integer')
        ->toHaveKey('unsigned', true)
        ->toHaveKey('nullable', true)
        ->toHaveKey('index', true)
        //
        ->and($columns)
        ->toHaveKey('file_file_type')
        ->and($columns['file_file_type'])
        ->toBeArray()
        ->toHaveKey('type', 'enum')
        ->toHaveKey('allowed', enum_to_names(LaruploadFileType::cases()))
        ->toHaveKey('nullable', true)
        ->toHaveKey('index', true)
        //
        ->and($columns)
        ->toHaveKey('file_file_mime_type')
        ->and($columns['file_file_mime_type'])
        ->toBeArray()
        ->toHaveKey('type', 'string')
        ->toHaveKey('length', 85)
        ->toHaveKey('nullable', true)
        //
        ->and($columns)
        ->toHaveKey('file_file_width')
        ->and($columns['file_file_width'])
        ->toBeArray()
        ->toHaveKey('type', 'integer')
        ->toHaveKey('unsigned', true)
        ->toHaveKey('nullable', true)
        //
        ->and($columns)
        ->toHaveKey('file_file_height')
        ->and($columns['file_file_height'])
        ->toBeArray()
        ->toHaveKey('type', 'integer')
        ->toHaveKey('unsigned', true)
        ->toHaveKey('nullable', true)
        //
        ->and($columns)
        ->toHaveKey('file_file_duration')
        ->and($columns['file_file_duration'])
        ->toBeArray()
        ->toHaveKey('type', 'integer')
        ->toHaveKey('unsigned', true)
        ->toHaveKey('nullable', true)
        ->toHaveKey('index', true)
        //
        ->and($columns)
        ->toHaveKey('file_file_dominant_color')
        ->and($columns['file_file_dominant_color'])
        ->toBeArray()
        ->toHaveKey('type', 'string')
        ->toHaveKey('length', 7)
        ->toHaveKey('nullable', true)
        //
        ->and($columns)
        ->toHaveKey('file_file_format')
        ->and($columns['file_file_format'])
        ->toBeArray()
        ->toHaveKey('type', 'string')
        ->toHaveKey('length', 85)
        ->toHaveKey('nullable', true)
        //
        ->and($columns)
        ->toHaveKey('file_file_cover')
        ->and($columns['file_file_cover'])
        ->toBeArray()
        ->toHaveKey('type', 'string')
        ->toHaveKey('length', 85)
        ->toHaveKey('nullable', true)
        //
        ->and($columns)
        ->not->toHaveKey('file_file_original_name');

});

it('will create all columns in light mode', function() {
    $columns = macroColumns(LaruploadMode::LIGHT);

    expect($columns)
        ->toBeArray()
        ->toHaveCount(2)
        ->and($columns)
        ->toHaveKey('file_file_name')
        ->and($columns['file_file_name'])
        ->toBeArray()
        ->toHaveKey('type', 'string')
        ->toHaveKey('length', 255)
        ->toHaveKey('nullable', true)
        //
        ->and($columns)
        ->toHaveKey('file_file_meta')
        ->and($columns['file_file_meta'])
        ->toBeArray()
        ->toHaveKey('type')
        ->toHaveKey('nullable', true)
        ->and($columns['file_file_meta']['type'])
        ->toBeIn(['text', 'json']);
});


it('will drop upload column in heavy mode', function() {
    $table = 'without_store_original_name_column';
    $builder = $this->app['db']->connection()->getSchemaBuilder();

    $builder->table($table, function(Blueprint $table) {
        $table->dropUpload('main_file', LaruploadMode::HEAVY);
    });

    $columns = $builder->getColumnListing($table);

    expect($columns)
        ->toBeArray()
        ->toHaveCount(3)
        ->toMatchArray([
            'id', 'created_at', 'updated_at'
        ]);
});

it('will drop upload column in light mode', function() {
    $table = 'upload_light';
    $builder = $this->app['db']->connection()->getSchemaBuilder();

    $builder->table($table, function(Blueprint $table) {
        $table->dropUpload('main_file', LaruploadMode::LIGHT);
    });

    $columns = $builder->getColumnListing($table);

    expect($columns)
        ->toBeArray()
        ->toHaveCount(3)
        ->toMatchArray([
            'id', 'created_at', 'updated_at'
        ]);
});


it('will create file_original_name column in heavy mode', function() {
    config()->set('larupload.store-original-file-name', true);

    $columns = macroColumns(LaruploadMode::HEAVY);

    expect($columns)
        ->toHaveKey('file_file_original_name')
        ->and($columns['file_file_original_name'])
        ->toBeArray()
        ->toHaveKey('type', 'string')
        ->toHaveKey('length', 85)
        ->toHaveKey('nullable', true);
});

it('will drop upload file_original_name column in heavy mode', function() {
    config()->set('larupload.store-original-file-name', true);

    $table = 'heavy';

    $this->app['db']->connection()
        ->getSchemaBuilder()
        ->create($table, function(Blueprint $table) {
            $table->id();
            $table->upload('main_file', LaruploadMode::HEAVY);
            $table->timestamps();
        });


    $builder = $this->app['db']->connection()->getSchemaBuilder();


    $builder->table($table, function(Blueprint $table) {
        $table->dropUpload('main_file', LaruploadMode::HEAVY);
    });

    $columns = $builder->getColumnListing($table);

    expect($columns)
        ->toBeArray()
        ->toHaveCount(3)
        ->toMatchArray([
            'id', 'created_at', 'updated_at'
        ]);
});
