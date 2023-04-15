<?php

use Illuminate\Support\Str;
use Mostafaznv\Larupload\Enums\LaruploadSecureIdsMethod;
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

it('will delete all files after deleting model when secure-ids is enabled', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    config()->set('larupload.secure-ids', LaruploadSecureIdsMethod::ULID);

    $model = $model::class;
    $model = save(new $model, jpg());

    $paths = urlsToPath($model->main_file);
    $id = $model->attachment('main_file')->meta('id');

    expect(Str::isUlid($id))->toBeTrue();

    foreach ($paths as $path) {
        expect($path)->toContain($id)
            ->and(file_exists($path))->toBeTrue();
    }

    $model->delete();

    foreach ($paths as $path) {
        expect(file_exists($path))->toBeFalse();
    }

})->with('models');

it('wont delete files when preserveFiles is enabled', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    config()->set('larupload.preserve-files', true);

    $model = $model::class;
    $model = save(new $model, jpg());
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

    expect($model->attachment('main_file')->url())
        ->toBeTruthy()
        ->toBeString()
        ->toBeExists();

    $model->main_file = LARUPLOAD_NULL;
    $model->save();

    expect($model->attachment('main_file')->url())
        ->toBeNull()
        ->and($model->attachment('main_file')->meta('name'))
        ->toBeNull();

})->with('models');

it('will delete files by detach function', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $model = save($model, jpg());

    expect($model->attachment('main_file')->url())
        ->toBeTruthy()
        ->toBeString()
        ->toBeExists();

    $model->attachment('main_file')->detach();
    $model->save();

    expect($model->attachment('main_file')->url())
        ->toBeNull()
        ->and($model->attachment('main_file')->meta('name'))
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

it("won't crash on delete if file doesn't exist in standalone mode", function() {
    Larupload::init('uploader')->upload(jpg());

    $path = public_path('uploads/uploader');

    expect(is_dir($path))->toBeTrue();

    rmRf($path);

    expect(is_dir($path))->toBeFalse();

    $result = Larupload::init('uploader')->delete();

    expect($result)->toBeFalse();
});

