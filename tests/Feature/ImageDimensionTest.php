<?php

use Illuminate\Http\UploadedFile;
use Mostafaznv\Larupload\Enums\LaruploadMediaStyle;
use Mostafaznv\Larupload\Larupload;
use Mostafaznv\Larupload\Test\Support\Enums\LaruploadTestModels;
use Mostafaznv\Larupload\Test\Support\LaruploadTestConsts;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadHeavyTestModel;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadLightTestModel;


it('will calculate image width and height correctly', function () {
    $model = LaruploadTestModels::HEAVY->instance();
    $model = save($model, jpg());


    # width
    $fileWidth = LaruploadTestConsts::IMAGE_DETAILS['jpg']['width'];
    $metaWidth = $model->attachment('main_file')->meta('width');
    expect($metaWidth)->toBe($fileWidth);


    # height
    $fileHeight = LaruploadTestConsts::IMAGE_DETAILS['jpg']['height'];
    $metaHeight = $model->attachment('main_file')->meta('height');
    expect($metaHeight)->toBe($fileHeight);
});

it('will calculate dimension of different styles correctly', function (UploadedFile $file) {
    $model = LaruploadTestModels::LIGHT->instance();
    $model->withAllImages();
    $model = save($model, jpg());

    $cover = urlToImage($model->attachment('main_file')->url('cover'));
    $small = urlToImage($model->attachment('main_file')->url('small'));
    $medium = urlToImage($model->attachment('main_file')->url('medium'));
    $landscape = urlToImage($model->attachment('main_file')->url('landscape'));
    $portrait = urlToImage($model->attachment('main_file')->url('portrait'));
    $exact = urlToImage($model->attachment('main_file')->url('exact'));
    $auto = urlToImage($model->attachment('main_file')->url('auto'));


    expect($cover->getSize()->getWidth())->toBe(500)
        ->and($cover->getSize()->getHeight())->toBe(500)
        # small
        ->and($small->getSize()->getWidth())->toBe(200)
        ->and($small->getSize()->getHeight())->toBe(200)
        # medium
        ->and($medium->getSize()->getWidth())->toBe(800)
        ->and($medium->getSize()->getHeight())->toBe(588)
        # landscape
        ->and($landscape->getSize()->getWidth())->toBe(400)
        ->and($landscape->getSize()->getHeight())->toBe(294)
        # portrait
        ->and($portrait->getSize()->getWidth())->toBe(545)
        ->and($portrait->getSize()->getHeight())->toBe(400)
        # exact
        ->and($exact->getSize()->getWidth())->toBe(300)
        ->and($exact->getSize()->getHeight())->toBe(190)
        # auto
        ->and($auto->getSize()->getWidth())->toBe(300)
        ->and($auto->getSize()->getHeight())->toBe(220);

})->with([
    'jpg'  => fn() => jpg(),
    'webp' => fn() => webp(),
]);

it('will calculate image width and height correctly in standalone mode', function () {
    $upload = Larupload::init('uploader')->upload(jpg());

    expect($upload->meta->width)
        ->toBe(LaruploadTestConsts::IMAGE_DETAILS['jpg']['width'])
        ->and($upload->meta->height)
        ->toBe(LaruploadTestConsts::IMAGE_DETAILS['jpg']['height']);
});

it('will calculate dimension of different styles correctly in standalone mode', function () {
    $upload = Larupload::init('uploader')
        ->image('small', 200, 200, LaruploadMediaStyle::CROP)
        ->image('medium', 800, 800, LaruploadMediaStyle::AUTO)
        ->image('landscape', 400, null, LaruploadMediaStyle::SCALE_HEIGHT)
        ->image('portrait', null, 400, LaruploadMediaStyle::SCALE_WIDTH)
        ->image('exact', 300, 190, LaruploadMediaStyle::FIT)
        ->image('auto', 300, 190, LaruploadMediaStyle::AUTO)
        ->upload(jpg());

    $cover = urlToImage($upload->cover);
    $small = urlToImage($upload->small);
    $medium = urlToImage($upload->medium);
    $landscape = urlToImage($upload->landscape);
    $portrait = urlToImage($upload->portrait);
    $exact = urlToImage($upload->exact);
    $auto = urlToImage($upload->auto);


    expect($cover->getSize()->getWidth())->toBe(500)
        ->and($cover->getSize()->getHeight())->toBe(500)
        # small
        ->and($small->getSize()->getWidth())->toBe(200)
        ->and($small->getSize()->getHeight())->toBe(200)
        # medium
        ->and($medium->getSize()->getWidth())->toBe(800)
        ->and($medium->getSize()->getHeight())->toBe(588)
        # landscape
        ->and($landscape->getSize()->getWidth())->toBe(400)
        ->and($landscape->getSize()->getHeight())->toBe(294)
        # portrait
        ->and($portrait->getSize()->getWidth())->toBe(545)
        ->and($portrait->getSize()->getHeight())->toBe(400)
        # exact
        ->and($exact->getSize()->getWidth())->toBe(300)
        ->and($exact->getSize()->getHeight())->toBe(190)
        # auto
        ->and($auto->getSize()->getWidth())->toBe(300)
        ->and($auto->getSize()->getHeight())->toBe(220);
});
