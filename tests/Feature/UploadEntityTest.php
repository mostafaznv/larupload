<?php

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
