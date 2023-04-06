<?php

use Mostafaznv\Larupload\Enums\LaruploadSecureIdsMethod;
use Mostafaznv\Larupload\Test\Support\LaruploadTestConsts;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadHeavyTestModel;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadLightTestModel;


it('will update attachment successfully', function (LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $model = save($model, jpg(), png());
    $jpg = LaruploadTestConsts::IMAGE_DETAILS['jpg'];
    $png = LaruploadTestConsts::IMAGE_DETAILS['png'];
    $webp = LaruploadTestConsts::IMAGE_DETAILS['webp'];

    $original = $model->main_file->url();
    $cover = $model->main_file->url('cover');
    $landscape = $model->main_file->url('landscape');

    expect($original)
        ->toBeExists()
        ->and($cover)
        ->toBeExists()
        ->and($landscape)
        ->toBeExists()
        //
        ->and($model->main_file->meta())
        ->toHaveProperty('name', $jpg['name']['hash'])
        ->toHaveProperty('cover', $png['name']['hash'])
        ->toHaveProperty('size', $jpg['size'])
        ->toHaveProperty('format', 'jpg')
        ->toHaveProperty('width', $jpg['width'])
        ->toHaveProperty('height', $jpg['height'])
        ->toHaveProperty('dominant_color', $jpg['color']);

    $model->main_file->attach(webp(), jpg());
    $model->save();

    $updatedOriginal = $model->main_file->url();
    $updatedCover = $model->main_file->url('cover');
    $updatedLandscape = $model->main_file->url('landscape');

    expect($updatedOriginal != $original)
        ->toBeTrue()
        ->and($updatedCover != $cover)
        ->toBeTrue()
        ->and($updatedLandscape != $landscape)
        ->toBeTrue()
        //
        ->and($original)
        ->toNotExists()
        ->and($cover)
        ->toNotExists()
        ->and($landscape)
        ->toNotExists()
        //
        ->and($updatedOriginal)
        ->toBeExists()
        ->and($updatedCover)
        ->toBeExists()
        ->and($updatedLandscape)
        ->toBeExists()
        //
        ->and($model->main_file->meta())
        ->toHaveProperty('name', $webp['name']['hash'])
        ->toHaveProperty('cover', $jpg['name']['hash'])
        ->toHaveProperty('size', $webp['size'])
        ->toHaveProperty('format', 'webp')
        ->toHaveProperty('width', $webp['width'])
        ->toHaveProperty('height', $webp['height'])
        ->toHaveProperty('dominant_color', $webp['color']);

})->with('models');

it('wont change id of attachment during update', function (LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $model = save($model, jpg(), png());

    $original = $model->main_file->url();
    $cover = $model->main_file->url('cover');
    $landscape = $model->main_file->url('landscape');
    $id = $model->main_file->meta('id');

    expect($original)
        ->toContain($id)
        ->and($cover)
        ->toContain($id)
        ->and($landscape)
        ->toContain($id);

    $model->main_file->attach(webp(), jpg());
    $model->save();

    $updatedOriginal = $model->main_file->url();
    $updatedCover = $model->main_file->url('cover');
    $updatedLandscape = $model->main_file->url('landscape');
    $updatedId = $model->main_file->meta('id');

    expect($updatedId)->toBe($id)
        //
        ->and($updatedOriginal)
        ->toContain($updatedId)
        ->and($updatedCover)
        ->toContain($updatedId)
        ->and($updatedLandscape)
        ->toContain($updatedId);

})->with('models');

it('wont change id of attachment during update when secure-ids is enabled', function (LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    config()->set('larupload.secure-ids', LaruploadSecureIdsMethod::ULID);

    $model = $model::class;
    $model = save(new $model, jpg(), png());

    $original = $model->main_file->url();
    $cover = $model->main_file->url('cover');
    $landscape = $model->main_file->url('landscape');
    $id = $model->main_file->meta('id');

    expect($original)
        ->toContain($id)
        ->and($cover)
        ->toContain($id)
        ->and($landscape)
        ->toContain($id);

    $model->main_file->attach(webp(), jpg());
    $model->save();

    $updatedOriginal = $model->main_file->url();
    $updatedCover = $model->main_file->url('cover');
    $updatedLandscape = $model->main_file->url('landscape');
    $updatedId = $model->main_file->meta('id');

    expect($updatedId)->toBe($id)
        //
        ->and($updatedOriginal)
        ->toContain($updatedId)
        ->and($updatedCover)
        ->toContain($updatedId)
        ->and($updatedLandscape)
        ->toContain($updatedId);

})->with('models');
