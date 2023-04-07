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

    $url = $model->attachment('main_file')->url('cover');
    $meta = $model->attachment('main_file')->meta();

    expect($url)
        ->toBeTruthy()
        ->toBeString()
        ->toBeExists()
        ->and($meta)
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

    $fileCover = LaruploadTestConsts::IMAGE_DETAILS['jpg']['name']['hash'];
    $metaCover = $model->attachment('main_file')->meta('cover');

    expect($metaCover)->toBe($fileCover);

    $model->attachment('main_file')->cover()->update(png());
    $model->save();

    $fileCover = LaruploadTestConsts::IMAGE_DETAILS['png']['name']['hash'];
    $metaCover = $model->attachment('main_file')->meta('cover');

    expect($metaCover)->toBe($fileCover);

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

    $url = $model->attachment('main_file')->url('cover');
    $metaCover = $model->attachment('main_file')->meta('cover');
    $fileCover = LaruploadTestConsts::IMAGE_DETAILS['jpg']['name']['hash'];

    expect($url)
        ->toBeExists()
        ->and($metaCover)
        ->toBe($fileCover);

    $model->attachment('main_file')->cover()->update(png());
    $model->save();

    $url = $model->attachment('main_file')->url('cover');
    $metaCover = $model->attachment('main_file')->meta('cover');
    $fileCover = LaruploadTestConsts::IMAGE_DETAILS['png']['name']['hash'];

    expect($url)
        ->toBeExists()
        ->and($metaCover)
        ->toBe($fileCover);

})->with('models');

it('will delete cover', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $model = save($model, jpg());

    $url = $model->attachment('main_file')->url('cover');
    $meta = $model->attachment('main_file')->meta('cover');

    expect($url)
        ->toBeTruthy()
        ->toBeString()
        ->toBeExists()
        ->and($meta)
        ->toBeTruthy();

    $model->attachment('main_file')->cover()->detach();
    $model->save();

    $url = $model->attachment('main_file')->url('cover');
    $meta = $model->attachment('main_file')->meta('cover');

    expect($url)
        ->toBeNull()
        ->and($meta)
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

    $oldCover = $model->attachment('main_file')->url('cover');
    $meta = $model->attachment('main_file')->meta('cover');

    expect($oldCover)
        ->toBeTruthy()
        ->toBeString()
        ->toBeExists()
        ->and($meta)
        ->toBeTruthy();

    $model->attachment('main_file')->cover()->detach();
    $model->save();

    $url = $model->attachment('main_file')->url('cover');
    $cover = $model->attachment('main_file')->meta('cover');

    expect($oldCover)
        ->toNotExists()
        ->and($url)
        ->toBeNull()
        ->and($cover)
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

    $cover = $model->attachment('main_file')->url('cover');

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
