<?php

use Mostafaznv\Larupload\Enums\LaruploadMode;


it('will create all columns in heavy mode', function() {
    $columns = macroColumns(LaruploadMode::HEAVY);

    expect($columns)
        ->toBeArray()
        ->toHaveCount(11)
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
        //
        ->and($columns)
        ->toHaveKey('file_file_type')
        ->and($columns['file_file_type'])
        ->toBeArray()
        ->toHaveKey('type', 'string')
        ->toHaveKey('length', 85)
        ->toHaveKey('nullable', true)
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
        ->toHaveKey('nullable', true);

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
