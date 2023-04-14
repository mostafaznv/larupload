<?php

use Mostafaznv\Larupload\Enums\LaruploadSecureIdsMethod;
use Mostafaznv\Larupload\Test\Support\LaruploadTestConsts;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadHeavyTestModel;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadLightTestModel;
use Mostafaznv\Larupload\Test\Support\TestAttachmentBuilder;


it('will update attachment successfully', function (LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $model->withAllImages();
    $model = save($model, jpg(), png());
    $jpg = LaruploadTestConsts::IMAGE_DETAILS['jpg'];
    $png = LaruploadTestConsts::IMAGE_DETAILS['png'];
    $webp = LaruploadTestConsts::IMAGE_DETAILS['webp'];

    $attachment = $model->attachment('main_file');
    $original = $attachment->url();
    $cover = $attachment->url('cover');
    $landscape = $attachment->url('landscape');

    expect($original)
        ->toBeExists()
        ->and($cover)
        ->toBeExists()
        ->and($landscape)
        ->toBeExists()
        //
        ->and($attachment->meta())
        ->toHaveProperty('name', $jpg['name']['hash'])
        ->toHaveProperty('cover', $png['name']['hash'])
        ->toHaveProperty('size', $jpg['size'])
        ->toHaveProperty('format', 'jpg')
        ->toHaveProperty('width', $jpg['width'])
        ->toHaveProperty('height', $jpg['height'])
        ->toHaveProperty('dominant_color', $jpg['color']);

    $model->attachment('main_file')->attach(webp(), jpg());
    $model->save();

    $attachment = $model->attachment('main_file');
    $updatedOriginal = $attachment->url();
    $updatedCover = $attachment->url('cover');
    $updatedLandscape = $attachment->url('landscape');

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
        ->and($attachment->meta())
        ->toHaveProperty('name', $webp['name']['hash'])
        ->toHaveProperty('cover', $jpg['name']['hash'])
        ->toHaveProperty('size', $webp['size'])
        ->toHaveProperty('format', 'webp')
        ->toHaveProperty('width', $webp['width'])
        ->toHaveProperty('height', $webp['height'])
        ->toHaveProperty('dominant_color', $webp['color']);

})->with('models');

it('wont change id of attachment during update', function (LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $model->setAttachments(
        TestAttachmentBuilder::make($model->mode)->withLandscapeImage()->toArray()
    );
    $model = save($model, jpg(), png());

    $attachment = $model->attachment('main_file');
    $original = $attachment->url();
    $cover = $attachment->url('cover');
    $landscape = $attachment->url('landscape');
    $id = $attachment->meta('id');

    expect($original)
        ->toContain($id)
        ->and($cover)
        ->toContain($id)
        ->and($landscape)
        ->toContain($id);

    $model->attachment('main_file')->attach(webp(), jpg());
    $model->save();

    $attachment = $model->attachment('main_file');
    $updatedOriginal = $attachment->url();
    $updatedCover = $attachment->url('cover');
    $updatedLandscape = $attachment->url('landscape');
    $updatedId = $attachment->meta('id');

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

    $model = new ($model::class);
    $model->setAttachments(
        TestAttachmentBuilder::make($model->mode)->withLandscapeImage()->toArray()
    );

    $model = save($model, jpg(), png());
    $attachment = $model->attachment('main_file');
    $original = $attachment->url();
    $cover = $attachment->url('cover');
    $landscape = $attachment->url('landscape');
    $id = $attachment->meta('id');

    expect($original)
        ->toContain($id)
        ->and($cover)
        ->toContain($id)
        ->and($landscape)
        ->toContain($id);

    $model->attachment('main_file')->attach(webp(), jpg());
    $model->save();

    $attachment = $model->attachment('main_file');
    $updatedOriginal = $attachment->url();
    $updatedCover = $attachment->url('cover');
    $updatedLandscape = $attachment->url('landscape');
    $updatedId = $attachment->meta('id');

    expect($updatedId)->toBe($id)
        //
        ->and($updatedOriginal)
        ->toContain($updatedId)
        ->and($updatedCover)
        ->toContain($updatedId)
        ->and($updatedLandscape)
        ->toContain($updatedId);

})->with('models');
