<?php

use Mostafaznv\Larupload\Enums\LaruploadImageLibrary;
use Mostafaznv\Larupload\Enums\LaruploadSecureIdsMethod;
use Mostafaznv\Larupload\Larupload;
use Mostafaznv\Larupload\Test\Support\LaruploadTestConsts;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadHeavyTestModel;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadLightTestModel;
use Mostafaznv\Larupload\Test\Support\TestAttachmentBuilder;

beforeEach(function() {
    config()->set('larupload.optimize-image.enable', true);
});

it('will optimize jpg', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $model->setAttachments(
        TestAttachmentBuilder::make($model->mode)->withLandscapeImage()->toArray()
    );

    $model = save($model, jpg());
    $details = LaruploadTestConsts::IMAGE_DETAILS['jpg'];

    $attachment = $model->attachment('main_file');

    expect($attachment->url())
        ->toBeString()
        ->toBeTruthy()
        ->toBeExists()
        ->and($attachment->url('cover'))
        ->toBeString()
        ->toBeTruthy()
        ->toBeExists()
        ->and($attachment->url('landscape'))
        ->toBeString()
        ->toBeTruthy()
        ->toBeExists()
        ->and($attachment->meta())
        ->toHaveProperty('width', $details['width'])
        ->toHaveProperty('height', $details['height'])
        ->and($attachment->meta('size'))
        ->toBeLessThan($details['size']);

})->with('models');

it('will optimize png', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $model->setAttachments(
        TestAttachmentBuilder::make($model->mode)->withLandscapeImage()->toArray()
    );

    $model = save($model, png());
    $details = LaruploadTestConsts::IMAGE_DETAILS['png'];

    $attachment = $model->attachment('main_file');

    expect($attachment->url())
        ->toBeString()
        ->toBeTruthy()
        ->toBeExists()
        ->and($attachment->url('cover'))
        ->toBeString()
        ->toBeTruthy()
        ->toBeExists()
        ->and($attachment->url('landscape'))
        ->toBeString()
        ->toBeTruthy()
        ->toBeExists()
        ->and($attachment->meta())
        ->toHaveProperty('width', $details['width'])
        ->toHaveProperty('height', $details['height'])
        ->and($attachment->meta('size'))
        ->toBeLessThan($details['size']);

})->with('models');

it('will optimize webp', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $model->setAttachments(
        TestAttachmentBuilder::make($model->mode)->withLandscapeImage()->toArray()
    );

    $model = save($model, webp());
    $details = LaruploadTestConsts::IMAGE_DETAILS['webp'];

    $attachment = $model->attachment('main_file');

    expect($attachment->url())
        ->toBeString()
        ->toBeTruthy()
        ->toBeExists()
        ->and($attachment->url('cover'))
        ->toBeString()
        ->toBeTruthy()
        ->toBeExists()
        ->and($attachment->url('landscape'))
        ->toBeString()
        ->toBeTruthy()
        ->toBeExists()
        ->and($attachment->meta())
        ->toHaveProperty('width', $details['width'])
        ->toHaveProperty('height', $details['height'])
        ->and($attachment->meta('size'))
        ->not->toBe($details['size']);

})->with('models');

/*it('will optimize svg', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    config()->set('larupload.image-processing-library', LaruploadImageLibrary::IMAGICK);

    $details = LaruploadTestConsts::IMAGE_DETAILS['svg'];

    $model = new ($model::class);
    $model->setAttachments(
        TestAttachmentBuilder::make($model->mode)->withLandscapeImage()->toArray()
    );
    $model = save($model, svg());

    $attachment = $model->attachment('main_file');

    expect($attachment->url())
        ->toBeString()
        ->toBeTruthy()
        ->toBeExists()
        ->and($attachment->url('cover'))
        ->toBeString()
        ->toBeTruthy()
        ->toBeExists()
        ->and($attachment->url('landscape'))
        ->toBeString()
        ->toBeTruthy()
        ->toBeExists()
        ->and($attachment->meta())
        ->toHaveProperty('width', $details['width'])
        ->toHaveProperty('height', $details['height'])
        ->and($attachment->meta('size'))
        ->toBeLessThan($details['size']);

})->with('models');*/

it('wont optimize non-image files', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $model = save($model, mp3());

    $details = LaruploadTestConsts::AUDIO_DETAILS;
    $attachment = $model->attachment('main_file');

    expect($attachment->url())
        ->toBeString()
        ->toBeTruthy()
        ->toBeExists()
        ->and($attachment->meta('size'))
        ->toBe($details['size']);

    $model = save($model, $pdf = pdf());
    $attachment = $model->attachment('main_file');

    expect($attachment->url())
        ->toBeString()
        ->toBeTruthy()
        ->toBeExists()
        ->and($attachment->meta('size'))
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
        ->and($upload->meta->size)
        ->toBeLessThan($details['size']);
});

it('will optimize images when secure-ids is enabled', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    config()->set('larupload.secure-ids', LaruploadSecureIdsMethod::ULID);

    $model = new ($model::class);
    $model->setAttachments(
        TestAttachmentBuilder::make($model->mode)->withLandscapeImage()->toArray()
    );
    $model = save($model, jpg());

    $details = LaruploadTestConsts::IMAGE_DETAILS['jpg'];
    $attachment = $model->attachment('main_file');

    expect($attachment->url())
        ->toBeString()
        ->toBeTruthy()
        ->toBeExists()
        ->and($attachment->url('cover'))
        ->toBeString()
        ->toBeTruthy()
        ->toBeExists()
        ->and($attachment->url('landscape'))
        ->toBeString()
        ->toBeTruthy()
        ->toBeExists()
        ->and($attachment->meta())
        ->toHaveProperty('width', $details['width'])
        ->toHaveProperty('height', $details['height'])
        ->and($attachment->meta('size'))
        ->toBeLessThan($details['size']);

})->with('models');
