<?php

use Mostafaznv\Larupload\Enums\LaruploadImageLibrary;
use Mostafaznv\Larupload\Larupload;
use Mostafaznv\Larupload\Test\Support\LaruploadTestConsts;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadHeavyTestModel;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadLightTestModel;

beforeEach(function() {
    config()->set('larupload.optimize-image.enable', true);
});

it('will optimize jpg', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $model = save($model, jpg());
    $details = LaruploadTestConsts::IMAGE_DETAILS['jpg'];

    expect($model->main_file->url())
        ->toBeString()
        ->toBeTruthy()
        ->toBeExists()
        ->and($model->main_file->url('cover'))
        ->toBeString()
        ->toBeTruthy()
        ->toBeExists()
        ->and($model->main_file->url('landscape'))
        ->toBeString()
        ->toBeTruthy()
        ->toBeExists()
        ->and($model->main_file->meta())
        ->toHaveProperty('width', $details['width'])
        ->toHaveProperty('height', $details['height'])
        ->and($model->main_file->meta('size'))
        ->toBeLessThan($details['size']);

})->with('models');

it('will optimize png', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $model = save($model, png());
    $details = LaruploadTestConsts::IMAGE_DETAILS['png'];

    expect($model->main_file->url())
        ->toBeString()
        ->toBeTruthy()
        ->toBeExists()
        ->and($model->main_file->url('cover'))
        ->toBeString()
        ->toBeTruthy()
        ->toBeExists()
        ->and($model->main_file->url('landscape'))
        ->toBeString()
        ->toBeTruthy()
        ->toBeExists()
        ->and($model->main_file->meta())
        ->toHaveProperty('width', $details['width'])
        ->toHaveProperty('height', $details['height'])
        ->and($model->main_file->meta('size'))
        ->toBeLessThan($details['size']);

})->with('models');

it('will optimize webp', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $model = save($model, webp());
    $details = LaruploadTestConsts::IMAGE_DETAILS['webp'];

    expect($model->main_file->url())
        ->toBeString()
        ->toBeTruthy()
        ->toBeExists()
        ->and($model->main_file->url('cover'))
        ->toBeString()
        ->toBeTruthy()
        ->toBeExists()
        ->and($model->main_file->url('landscape'))
        ->toBeString()
        ->toBeTruthy()
        ->toBeExists()
        ->and($model->main_file->meta())
        ->toHaveProperty('width', $details['width'])
        ->toHaveProperty('height', $details['height'])
        ->and($model->main_file->meta('size'))
        ->not->toBe($details['size']);

})->with('models');

it('will optimize svg', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    config()->set('larupload.image-processing-library', LaruploadImageLibrary::IMAGICK);

    $model = $model::class;
    $model = save(new $model, svg());
    $details = LaruploadTestConsts::IMAGE_DETAILS['svg'];

    expect($model->main_file->url())
        ->toBeString()
        ->toBeTruthy()
        ->toBeExists()
        ->and($model->main_file->url('cover'))
        ->toBeString()
        ->toBeTruthy()
        ->toBeExists()
        ->and($model->main_file->url('landscape'))
        ->toBeString()
        ->toBeTruthy()
        ->toBeExists()
        ->and($model->main_file->meta())
        ->toHaveProperty('width', $details['width'])
        ->toHaveProperty('height', $details['height'])
        ->and($model->main_file->meta('size'))
        ->toBeLessThan($details['size']);

})->with('models');

it('wont optimize non-image files', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $model = save($model, mp3());
    $details = LaruploadTestConsts::AUDIO_DETAILS;

    expect($model->main_file->url())
        ->toBeString()
        ->toBeTruthy()
        ->toBeExists()
        ->and($model->main_file->meta('size'))
        ->toBe($details['size']);

    $model = save($model, $pdf = pdf());

    expect($model->main_file->url())
        ->toBeString()
        ->toBeTruthy()
        ->toBeExists()
        ->and($model->main_file->meta('size'))
        ->toBe($pdf->getSize());

})->with('models');

it('will optimize images in standalone mode', function() {
    $details = LaruploadTestConsts::IMAGE_DETAILS['jpg'];

    $upload = Larupload::init('uploader')
        ->image('thumbnail', 1000, 750)
        ->upload(jpg());

    expect($upload->original)
        ->toBeString()
        ->toBeTruthy()
        ->toBeExists()
        ->and($upload->thumbnail)
        ->toBeString()
        ->toBeTruthy()
        ->toBeExists()
        ->and($upload->cover)
        ->toBeString()
        ->toBeTruthy()
        ->toBeExists()
        ->and($upload->meta)
        ->toHaveProperty('width', $details['width'])
        ->toHaveProperty('height', $details['height'])
        ->and($upload->meta)
        ->toBeLessThan($details['size']);;
});
