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

it('wont upload cover if file is not an image', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $model = save($model, pdf(), zip());

    $url = $model->attachment('main_file')->url('cover');
    $meta = $model->attachment('main_file')->meta();

    expect($url)
        ->toBeNull()
        ->and($meta)
        ->toHaveProperty('cover', null)
        ->toHaveProperty('dominant_color', null);

})->with('models');

it('wont upload cover if file is not an image in standalone mode', function() {
    $upload = Larupload::init('uploader')
        ->upload(pdf(), zip());

    expect($upload->cover)
        ->toBeNull()
        ->and($upload->meta)
        ->toHaveProperty('cover', null)
        ->toHaveProperty('dominant_color', null);

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

it('wont update cover if meta file doesnt exist in standalone mode', function() {
    $upload = Larupload::init('uploader')->upload(jpg());

    expect($upload->meta->cover)
        ->toBe(LaruploadTestConsts::IMAGE_DETAILS['jpg']['name']['hash']);

    $meta = public_path('uploads/uploader/.meta');
    expect(file_exists($meta))->toBeTrue();

    unlink($meta);
    expect(file_exists($meta))->toBeFalse();

    $upload = Larupload::init('uploader')->changeCover(png());

    expect($upload)->toBeNull();
});

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

it('will clear dominant color after deleting cover for non-image files', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $model = save($model, pdf(), jpg());

    $dominantColor = $model->attachment('main_file')->meta('dominant_color');

    expect($dominantColor)->toBeTruthy();

    $model->attachment('main_file')->cover()->detach();
    $model->save();

    $dominantColor = $model->attachment('main_file')->meta('dominant_color');

    expect($dominantColor)->toBeNull();

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

it('wont delete cover if meta file doesnt exist in standalone mode', function() {
    $upload = Larupload::init('uploader')->upload(jpg());

    expect($upload->cover)->toBeExists();

    $meta = public_path('uploads/uploader/.meta');
    expect(file_exists($meta))->toBeTrue();

    unlink($meta);
    expect(file_exists($meta))->toBeFalse();

    $upload = Larupload::init('uploader')->deleteCover();

    expect($upload)->toBeNull();
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

it('wont upload if cover has an error', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    save($model, pdf(), png(2));

})->with('models')->throws(RuntimeException::class, 'The [main_file-cover] field has an error');

it('wont upload if cover has an error in standalone mode', function() {
    Larupload::init('uploader')->upload(pdf(), png(2));

})->throws(RuntimeException::class, 'The [uploader-cover] field has an error');

it('wont update cover if cover has an error', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $model = save($model, pdf());
    $model->attachment('main_file')->cover()->update(png(2));
    $model->save();
})->with('models')->throws(RuntimeException::class, 'The [main_file-cover] field has an error');;

it('wont update cover if cover has an error in standalone mode', function() {
    Larupload::init('uploader')->upload(jpg());
    Larupload::init('uploader')->changeCover(png(2));
})->throws(RuntimeException::class, 'The [uploader-cover] field has an error');;
