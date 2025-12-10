<?php

use Illuminate\Http\UploadedFile;
use Mostafaznv\Larupload\Enums\LaruploadImageLibrary;
use Mostafaznv\Larupload\Enums\LaruploadMode;
use Mostafaznv\Larupload\Enums\LaruploadSecureIdsMethod;
use Mostafaznv\Larupload\Larupload;
use Mostafaznv\Larupload\Test\Support\Enums\LaruploadTestModels;
use Mostafaznv\Larupload\Test\Support\LaruploadTestConsts;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadHeavyTestModel;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadLightTestModel;
use Mostafaznv\Larupload\Test\Support\TestAttachmentBuilder;


beforeEach(function () {
    config()->set('larupload.optimize-image.enable', true);
});


it('will optimize images', function (UploadedFile $file, array $details, LaruploadImageLibrary $library, LaruploadMode $mode) {
    config()->set('larupload.image-processing-library', $library);

    $model = $mode === LaruploadMode::HEAVY
        ? LaruploadTestModels::HEAVY->instance()
        : LaruploadTestModels::LIGHT->instance();

    $model->setAttachments(
        TestAttachmentBuilder::make($mode)->withLandscapeImage()->toArray()
    );

    $model = save($model, $file);
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
        ->toHaveProperty('height', $details['height']);

    if ($file->extension() === 'webp') {
        expect($attachment->meta('size'))
            ->not->toBe($details['size']);
    }
    else {
        expect($attachment->meta('size'))
            ->toBeLessThan($details['size']);
    }

})->with([
    'jpg'  => fn() => [
        jpg(),
        LaruploadTestConsts::IMAGE_DETAILS['jpg'],
        LaruploadImageLibrary::GD,
        LaruploadMode::LIGHT, # we use both models to ensure compatibility
    ],
    'png'  => fn() => [
        png(),
        LaruploadTestConsts::IMAGE_DETAILS['png'],
        LaruploadImageLibrary::GD,
        LaruploadMode::HEAVY,
    ],
    'webp' => fn() => [
        webp(),
        LaruploadTestConsts::IMAGE_DETAILS['webp'],
        LaruploadImageLibrary::GD,
        LaruploadMode::LIGHT,
    ],
    'svg'  => fn() => [
        svg(),
        LaruploadTestConsts::IMAGE_DETAILS['svg'],
        LaruploadImageLibrary::IMAGICK,
        LaruploadMode::HEAVY,
    ],
]);

it('wont optimize non-image files', function () {
    $model = LaruploadTestModels::HEAVY->instance();
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
});

it('will optimize images in standalone mode', function () {
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

it('will optimize images when secure-ids is enabled', function (LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
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
