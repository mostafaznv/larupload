<?php

use Mostafaznv\Larupload\DTOs\Style\ImageStyle;
use Mostafaznv\Larupload\Enums\LaruploadMediaStyle;
use Mostafaznv\Larupload\Enums\LaruploadNamingMethod;
use Mostafaznv\Larupload\Enums\LaruploadSecureIdsMethod;
use Mostafaznv\Larupload\Larupload;
use Mostafaznv\Larupload\Test\Support\LaruploadTestConsts;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadHeavyTestModel;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadLightTestModel;

it('will upload file with cover', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $model = save($model, pdf(), jpg());
    $details = LaruploadTestConsts::IMAGE_DETAILS['jpg'];

    expect($model->main_file->url('cover'))
        ->toBeTruthy()
        ->toBeString()
        ->toBeExists()
        ->and($model->main_file->meta())
        ->toHaveProperty('cover', $details['name']['hash'])
        ->toHaveProperty('dominant_color', $details['color']);

})->with('models');

it('will upload file with cover in standalone mode', function() {
    $upload = Larupload::init('uploader')
        ->namingMethod(LaruploadNamingMethod::HASH_FILE)
        ->upload(pdf(), jpg());

    $details = LaruploadTestConsts::IMAGE_DETAILS['jpg'];

    expect($upload->cover)
        ->toBeTruthy()
        ->toBeString()
        ->toBeExists()
        ->and($upload->meta)
        ->toHaveProperty('cover', $details['name']['hash'])
        ->toHaveProperty('dominant_color', $details['color']);
});

it('will update cover', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $model = save($model, jpg());

    expect($model->main_file->meta('cover'))
        ->toBe(LaruploadTestConsts::IMAGE_DETAILS['jpg']['name']['hash']);

    $model->main_file->updateCover(png());
    $model->save();

    expect($model->main_file->meta('cover'))
        ->toBe(LaruploadTestConsts::IMAGE_DETAILS['png']['name']['hash']);

})->with('models');

it('will update cover in standalone mode', function() {
    $upload = Larupload::init('uploader')
        ->namingMethod(LaruploadNamingMethod::HASH_FILE)
        ->upload(jpg());

    expect($upload->meta->cover)
        ->toBe(LaruploadTestConsts::IMAGE_DETAILS['jpg']['name']['hash']);

    $upload = Larupload::init('uploader')
        ->namingMethod(LaruploadNamingMethod::HASH_FILE)
        ->changeCover(png());

    expect($upload->meta->cover)
        ->toBe(LaruploadTestConsts::IMAGE_DETAILS['png']['name']['hash']);

});

it('will update cover when secure-ids is enabled', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    config()->set('larupload.secure-ids', LaruploadSecureIdsMethod::ULID);

    $model = $model::class;
    $model = save(new $model, jpg());

    expect($model->main_file->url('cover'))
        ->toBeExists()
        ->and($model->main_file->meta('cover'))
        ->toBe(LaruploadTestConsts::IMAGE_DETAILS['jpg']['name']['hash']);

    $model->main_file->updateCover(png());
    $model->save();

    expect($model->main_file->url('cover'))
        ->toBeExists()
        ->and($model->main_file->meta('cover'))
        ->toBe(LaruploadTestConsts::IMAGE_DETAILS['png']['name']['hash']);

})->with('models');

it('will delete cover', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $model = save($model, jpg());

    expect($model->main_file->url('cover'))
        ->toBeTruthy()
        ->toBeString()
        ->toBeExists()
        ->and($model->main_file->meta('cover'))
        ->toBeTruthy();

    $model->main_file->detachCover();
    $model->save();

    expect($model->main_file->url('cover'))
        ->toBeNull()
        ->and($model->main_file->meta('cover'))
        ->toBeNull();

})->with('models');

it('will delete cover in standalone mode', function() {
    $upload = Larupload::init('uploader')->upload(jpg());

    expect($upload->cover)
        ->toBeTruthy()
        ->toBeString()
        ->toBeExists()
        ->and($upload->meta->cover)
        ->toBeTruthy();

    $upload = Larupload::init('uploader')->deleteCover();

    expect($upload->cover)
        ->toBeNull()
        ->and($upload->meta->cover)
        ->toBeNull();

});

it('will delete cover when secure-ids is enabled', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    config()->set('larupload.secure-ids', LaruploadSecureIdsMethod::ULID);

    $model = $model::class;
    $model = save(new $model, jpg());

    $oldCover = $model->main_file->url('cover');

    expect($oldCover)
        ->toBeTruthy()
        ->toBeString()
        ->toBeExists()
        ->and($model->main_file->meta('cover'))
        ->toBeTruthy();

    $model->main_file->detachCover();
    $model->save();

    expect($oldCover)
        ->toNotExists()
        ->and($model->main_file->url('cover'))
        ->toBeNull()
        ->and($model->main_file->meta('cover'))
        ->toBeNull();

})->with('models');

it('can customize cover style', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    config()->set('larupload.cover-style', ImageStyle::make(
        name: 'cover',
        width: 200,
        height: 150,
        mode: LaruploadMediaStyle::FIT
    ));


    $model = $model::class;
    $model = save(new $model, jpg());

    $cover = $model->main_file->url('cover');

    expect($cover)
        ->toBeTruthy()
        ->toBeString()
        ->toBeExists();

    $image = urlToImage($cover);

    expect($image->getSize()->getWidth())
        ->toBe(200)
        ->and($image->getSize()->getHeight())
        ->toBe(150);

})->with('models');

it('can customize cover style in standalone mode', function() {
    $upload = Larupload::init('uploader')
        ->coverStyle('cover', 200, 150, LaruploadMediaStyle::CROP)
        ->upload(jpg());


    expect($upload->cover)
        ->toBeTruthy()
        ->toBeString()
        ->toBeExists();

    $image = urlToImage($upload->cover);

    expect($image->getSize()->getWidth())
        ->toBe(200)
        ->and($image->getSize()->getHeight())
        ->toBe(150);

});
