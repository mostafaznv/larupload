<?php

use FFMpeg\Format\Audio\Aac;
use FFMpeg\Format\Audio\Wav;
use Mostafaznv\Larupload\Enums\LaruploadSecureIdsMethod;
use Mostafaznv\Larupload\Storage\Attachment;
use Mostafaznv\Larupload\Test\Support\Enums\LaruploadTestModels;
use Mostafaznv\Larupload\Test\Support\LaruploadTestConsts;


beforeEach(function() {
    $this->model = LaruploadTestModels::HEAVY->instance();
    $this->attachment = Attachment::make('main_file')->disk('local');
});


it('can change generate-cover property', function() {
    $this->model->setAttachments([
        $this->attachment->generateCover(false)
    ]);

    $model = save($this->model, jpg());
    $attachment = $model->attachment('main_file');

    expect($attachment->url('cover'))
        ->toBeNull()
        ->and($attachment->meta('cover'))
        ->toBeNull();
});

it('can get latest status of generate-cover property', function() {
    $this->attachment->generateCover(false);
    $status = $this->attachment->generateCover;

    expect($status)->toBeFalse();

    $this->attachment->generateCover(true);
    $status = $this->attachment->generateCover;

    expect($status)->toBeTrue();
});

it('can return image registered styles', function() {
    $this->attachment->image('thumb', 400, 400);
    $this->attachment->image('retina', 800, 800);

    $styles = $this->attachment->imageStyles;

    expect($styles)
        ->toBeArray()
        ->toHaveCount(2)
        ->and(array_keys($styles))
        ->toBe([
            'thumb', 'retina'
        ]);
});

it('can return video registered styles', function() {
    $this->attachment->video('sd', 400, 400);
    $this->attachment->video('hd', 800, 800);

    $styles = $this->attachment->videoStyles;

    expect($styles)
        ->toBeArray()
        ->toHaveCount(2)
        ->and(array_keys($styles))
        ->toBe([
            'sd', 'hd'
        ]);
});

it('can return audio registered styles', function() {
    $this->attachment->audio('audio_wav', new Wav);
    $this->attachment->audio('audio_aac', new Aac());

    $styles = $this->attachment->audioStyles;

    expect($styles)
        ->toBeArray()
        ->toHaveCount(2)
        ->and(array_keys($styles))
        ->toBe([
            'audio_wav', 'audio_aac'
        ]);
});

it('can change dominant-color property', function() {
    $this->model->setAttachments([
        $this->attachment->dominantColor(false)
    ]);

    $model = save($this->model, jpg());
    $meta = $model->attachment('main_file')->meta('dominant_color');

    expect($meta)->toBeNull();
});

it('can change dominant-color-quality property', function() {
    $this->model->setAttachments([
        $this->attachment->dominantColorQuality(50)
    ]);

    $model = save($this->model, jpg());
    $color = $model->attachment('main_file')->meta('dominant_color');

    expect($color)->toBe('#262f48');
});

it('can change keep-old-files property', function() {
    $this->model->setAttachments([
        $this->attachment
            ->keepOldFiles(true)
            ->image('thumb', 400, 400),
    ]);


    $model = save($this->model, jpg());
    $paths = urlsToPath($model->main_file, ['cover']);

    foreach ($paths as $path) {
        expect(file_exists($path))->toBeTrue();
    }

    $model->attachment('main_file')->attach(png());
    $model->save();

    $newPaths = urlsToPath($model->main_file, ['cover']);


    foreach ($paths as $path) {
        expect(file_exists($path))->toBeTrue();
    }

    foreach ($newPaths as $path) {
        expect(file_exists($path))->toBeTrue();
    }
});

it('can change preserve-file property', function() {
    $this->model->setAttachments([
        $this->attachment->preserveFiles(true)
    ]);


    $model = save($this->model, jpg());
    $paths = urlsToPath($model->main_file);

    foreach ($paths as $path) {
        expect(file_exists($path))->toBeTrue();
    }

    $model->delete();

    foreach ($paths as $path) {
        expect(file_exists($path))->toBeTrue();
    }
});

it('can change optimize image status', function() {
    $this->model->setAttachments([
        $this->attachment->optimizeImage(true)
    ]);


    $model = save($this->model, jpg());

    $details = LaruploadTestConsts::IMAGE_DETAILS['jpg'];
    $attachment = $model->attachment('main_file');

    expect($attachment->meta())
        ->toHaveProperty('width', $details['width'])
        ->toHaveProperty('height', $details['height'])
        ->and($attachment->meta('size'))
        ->toBeLessThan($details['size']);
});

it('can change secure-ids property', function() {
    $this->model->setAttachments([
        $this->attachment->secureIdsMethod(LaruploadSecureIdsMethod::ULID)
    ]);


    $model = save($this->model, jpg());

    $attachment = $model->attachment('main_file');
    $id = $attachment->meta('id');

    expect(Str::isUlid($id))->toBeTrue();
});
