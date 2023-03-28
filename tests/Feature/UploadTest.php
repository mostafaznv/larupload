<?php

use Mostafaznv\Larupload\Enums\LaruploadFileType;
use Mostafaznv\Larupload\Enums\LaruploadNamingMethod;
use Mostafaznv\Larupload\Larupload;
use Mostafaznv\Larupload\Test\Support\LaruploadTestConsts;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadHeavyTestModel;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadLightTestModel;

$metaKeys = [
    'name', 'size', 'type', 'mime_type', 'width', 'height', 'duration', 'dominant_color', 'format', 'cover'
];


it('will upload image successfully', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) use ($metaKeys) {
    $model = save($model, jpg());
    $details = LaruploadTestConsts::IMAGE_DETAILS['jpg'];

    expect($model->main_file->url())
        ->toBeString()
        ->toBeTruthy()
        ->toBeExists()
        ->and($model->main_file->meta())
        ->toBeObject()
        ->toHaveKeys($metaKeys)
        ->toHaveProperty('name', $details['name']['hash'])
        ->toHaveProperty('size', $details['size'])
        ->toHaveProperty('type', LaruploadFileType::IMAGE->name)
        ->toHaveProperty('mime_type', $details['mime_type'])
        ->toHaveProperty('format', 'jpg')
        ->toHaveProperty('width', $details['width'])
        ->toHaveProperty('height', $details['height'])
        ->toHaveProperty('dominant_color', $details['color'])
        ->toHaveProperty('duration', null);

})->with('models');

it('will upload audio successfully', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) use ($metaKeys) {
    $model = save($model, mp3());
    $details = LaruploadTestConsts::AUDIO_DETAILS;

    expect($model->main_file->url())
        ->toBeString()
        ->toBeTruthy()
        ->toBeExists()
        ->and($model->main_file->meta())
        ->toBeObject()
        ->toHaveKeys($metaKeys)
        ->toHaveProperty('name', $details['name'])
        ->toHaveProperty('size', $details['size'])
        ->toHaveProperty('type', LaruploadFileType::AUDIO->name)
        ->toHaveProperty('mime_type', $details['mime_type'])
        ->toHaveProperty('format', 'mp3')
        ->toHaveProperty('duration', $details['duration']);

})->with('models');

it('will upload video successfully', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) use ($metaKeys) {
    $model = save($model, mp4());
    $details = LaruploadTestConsts::VIDEO_DETAILS;

    expect($model->main_file->url())
        ->toBeString()
        ->toBeTruthy()
        ->toBeExists()
        ->and($model->main_file->meta())
        ->toBeObject()
        ->toHaveKeys($metaKeys)
        ->toHaveProperty('name', $details['name'])
        ->toHaveProperty('size', $details['size'])
        ->toHaveProperty('type', LaruploadFileType::VIDEO->name)
        ->toHaveProperty('mime_type', $details['mime_type'])
        ->toHaveProperty('format', 'mp4')
        ->toHaveProperty('width', $details['width'])
        ->toHaveProperty('height', $details['height'])
        ->toHaveProperty('dominant_color', $details['color'])
        ->toHaveProperty('duration', $details['duration']);

})->with('models');

it('will upload with attach function', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) use ($metaKeys) {
    $model = $model::class;

    $model = new $model;
    $model->main_file->attach(jpg());
    $model->save();

    $details = LaruploadTestConsts::IMAGE_DETAILS['jpg'];

    expect($model->main_file->url())
        ->toBeString()
        ->toBeTruthy()
        ->toBeExists()
        ->and($model->main_file->meta())
        ->toBeObject()
        ->toHaveKeys($metaKeys)
        ->toHaveProperty('name', $details['name']['hash'])
        ->toHaveProperty('size', $details['size'])
        ->toHaveProperty('type', LaruploadFileType::IMAGE->name)
        ->toHaveProperty('mime_type', $details['mime_type'])
        ->toHaveProperty('format', 'jpg')
        ->toHaveProperty('width', $details['width'])
        ->toHaveProperty('height', $details['height'])
        ->toHaveProperty('dominant_color', $details['color'])
        ->toHaveProperty('duration', null);

})->with('models');

it('will upload image in standalone mode', function() use ($metaKeys) {
    $upload = Larupload::init('uploader')->upload(jpg());

    $details = LaruploadTestConsts::IMAGE_DETAILS['jpg'];

    expect($upload->original)
        ->toBeString()
        ->toBeTruthy()
        ->toBeExists()
        ->and($upload->meta)
        ->toBeObject()
        ->toHaveKeys($metaKeys)
        ->toHaveProperty('name')
        ->toHaveProperty('size', $details['size'])
        ->toHaveProperty('type', LaruploadFileType::IMAGE->name)
        ->toHaveProperty('mime_type', $details['mime_type'])
        ->toHaveProperty('format', 'jpg')
        ->toHaveProperty('width', $details['width'])
        ->toHaveProperty('height', $details['height'])
        ->toHaveProperty('dominant_color', $details['color'])
        ->toHaveProperty('duration', null)
        ->and($upload->meta->name)
        ->toContain($details['name']['slug']);
});

it('will upload audio in standalone mode', function() use ($metaKeys) {
    $upload = Larupload::init('uploader')
        ->namingMethod(LaruploadNamingMethod::HASH_FILE)
        ->upload(mp3());

    $details = LaruploadTestConsts::AUDIO_DETAILS;

    expect($upload->original)
        ->toBeString()
        ->toBeTruthy()
        ->toBeExists()
        ->and($upload->meta)
        ->toBeObject()
        ->toHaveKeys($metaKeys)
        ->toHaveProperty('name', $details['name'])
        ->toHaveProperty('size', $details['size'])
        ->toHaveProperty('type', LaruploadFileType::AUDIO->name)
        ->toHaveProperty('mime_type', $details['mime_type'])
        ->toHaveProperty('format', 'mp3')
        ->toHaveProperty('duration', $details['duration']);

});

it('will upload video in standalone mode', function() use ($metaKeys) {
    $upload = Larupload::init('uploader')
        ->namingMethod(LaruploadNamingMethod::HASH_FILE)
        ->upload(mp4());

    $details = LaruploadTestConsts::VIDEO_DETAILS;

    expect($upload->original)
        ->toBeString()
        ->toBeTruthy()
        ->toBeExists()
        ->and($upload->meta)
        ->toBeObject()
        ->toHaveKeys($metaKeys)
        ->toHaveProperty('name', $details['name'])
        ->toHaveProperty('size', $details['size'])
        ->toHaveProperty('type', LaruploadFileType::VIDEO->name)
        ->toHaveProperty('mime_type', $details['mime_type'])
        ->toHaveProperty('format', 'mp4')
        ->toHaveProperty('width', $details['width'])
        ->toHaveProperty('height', $details['height'])
        ->toHaveProperty('dominant_color', $details['color'])
        ->toHaveProperty('duration', $details['duration']);

});
