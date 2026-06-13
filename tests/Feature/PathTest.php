<?php

use Mostafaznv\Larupload\Enums\LaruploadSecureIdsMethod;
use Mostafaznv\Larupload\Storage\Attachment;
use Mostafaznv\Larupload\Test\Support\Enums\LaruploadTestModels;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadHeavyTestModel;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadLightTestModel;
use Mostafaznv\Larupload\Test\Support\TestAttachmentBuilder;


it('generates realpath for original file', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $model = save($model, jpg());
    $path = $model->attachment('main_file')->path();

    expect($path)
        ->toBeString()
        ->toContain('/original/')
        ->and(file_exists($path))
        ->toBeTrue();

})->with('models');

it('generates realpath for cover file', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $model = save($model, jpg());
    $path = $model->attachment('main_file')->path('cover');

    expect($path)
        ->toBeString()
        ->toContain('/cover/')
        ->and(file_exists($path))
        ->toBeTrue();

})->with('models');

it('generates realpath for custom image styles', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $model->setAttachments(
        TestAttachmentBuilder::make($model->mode)->withLandscapeImage()->toArray()
    );

    $model = save($model, jpg());
    $attachment = $model->attachment('main_file');
    $path = $model->attachment('main_file')->path('landscape');

    expect($path)
        ->toBeString()
        ->toContain('/landscape/')
        ->and(file_exists($path))
        ->toBeTrue();

})->with('models');

it('generates realpath for custom video styles', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $model->setAttachments(
        TestAttachmentBuilder::make($model->mode)->withSmallVideo()->toArray()
    );

    $model = save($model, mp4());
    $path = $model->attachment('main_file')->path('small');

    expect($path)
        ->toBeString()
        ->toContain('/small/')
        ->and(file_exists($path))
        ->toBeTrue();

})->with('models');

it('generates realpath for custom audio styles', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $model->setAttachments(
        TestAttachmentBuilder::make($model->mode)->withWavAudio()->toArray()
    );

    $model = save($model, mp3());
    $path = $model->attachment('main_file')->path('audio_wav');

    expect($path)
        ->toBeString()
        ->toContain('/audio-wav/')
        ->and(file_exists($path))
        ->toBeTrue();

})->with('models');

it('returns null for styles that do not exist', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $model = save($model, jpg());
    $path = $model->attachment('main_file')->path('not-exists');

    expect($path)->toBeNull();

})->with('models');

it('generates realpath for original file when secure-ids is enabled', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    config()->set('larupload.secure-ids', LaruploadSecureIdsMethod::ULID);

    $model = $model::class;
    $model = save(new $model, jpg());
    $path = $model->attachment('main_file')->path();

    expect($path)
        ->toBeString()
        ->and(file_exists($path))
        ->toBeTrue();

})->with('models');

it('returns null, if file is set and the value is `false`', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $model = save($model, jpg());
    $path = $model->attachment('main_file')->path();

    expect($path)
        ->toBeString()
        ->and(file_exists($path))
        ->toBeTrue();

    $model->main_file = false;
    $path = $model->attachment('main_file')->path();

    expect($path)->toBeNull();

})->with('models');

it('returns realpath when storage driver is not local', function() {
    Storage::fake('s3');
    $attachments = [
        Attachment::make('main_file')->disk('s3')
    ];

    $model = LaruploadTestModels::HEAVY->instance();
    $model->setAttachments($attachments);

    $model = save($model, jpg());
    $path = $model->attachment('main_file')->path();

    expect($path)
        ->toBeString()
        ->and(file_exists($path))
        ->toBeTrue();
});
