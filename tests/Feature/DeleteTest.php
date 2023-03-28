<?php

use Mostafaznv\Larupload\Larupload;
use Mostafaznv\Larupload\Test\Support\Enums\LaruploadTestModels;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadHeavyTestModel;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadLightTestModel;


it('will delete all files after deleting model', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $model = save($model, jpg());
    $paths = urlsToPath($model->main_file);

    foreach ($paths as $path) {
        expect(file_exists($path))->toBeTrue();
    }

    $model->delete();

    foreach ($paths as $path) {
        expect(file_exists($path))->toBeFalse();
    }

})->with('models');

it('wont delete files when preserveFiles is enabled', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $model = $model::class;
    $model = new $model;

    $model->main_file->preserveFiles(true);

    $model = save($model, jpg());
    $paths = urlsToPath($model->main_file);

    foreach ($paths as $path) {
        expect(file_exists($path))->toBeTrue();
    }

    $model->delete();

    foreach ($paths as $path) {
        expect(file_exists($path))->toBeTrue();
    }

})->with('models');

it('wont delete files when model has soft-delete trait', function() {
    $model = LaruploadTestModels::SOFT_DELETE->instance();
    $model = save($model, jpg());

    $paths = urlsToPath($model->main_file);

    foreach ($paths as $path) {
        expect(file_exists($path))->toBeTrue();
    }

    $model->delete();

    foreach ($paths as $path) {
        expect(file_exists($path))->toBeTrue();
    }
});

it('will delete files by setting LARUPLOAD_NULL to attribute', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $model = save($model, jpg());

    expect($model->main_file->url())
        ->toBeTruthy()
        ->toBeString()
        ->toBeExists();

    $model->main_file = LARUPLOAD_NULL;
    $model->save();

    expect($model->main_file->url())
        ->toBeNull()
        ->and($model->main_file->meta('name'))
        ->toBeNull();

})->with('models');

it('will delete files by detach function', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $model = save($model, jpg());

    expect($model->main_file->url())
        ->toBeTruthy()
        ->toBeString()
        ->toBeExists();

    $model->main_file->detach();
    $model->save();

    expect($model->main_file->url())
        ->toBeNull()
        ->and($model->main_file->meta('name'))
        ->toBeNull();

})->with('models');

it('will delete all files in standalone mode', function() {
    $upload = Larupload::init('uploader')->upload(jpg());

    $baseUrl = url('/');
    $url = str_replace($baseUrl, '', $upload->original);
    $path = public_path($url);

    expect(file_exists($path))->toBeTrue();

    Larupload::init('uploader')->delete();

    expect(file_exists($path))->toBeFalse();
});
