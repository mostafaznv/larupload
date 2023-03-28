<?php

use Mostafaznv\Larupload\Enums\LaruploadMediaStyle;
use Mostafaznv\Larupload\Larupload;
use Mostafaznv\Larupload\Test\Support\LaruploadTestConsts;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadHeavyTestModel;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadLightTestModel;

it('will calculate image width correctly', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $model = save($model, jpg());

    expect($model->main_file->meta('width'))
        ->toBe(LaruploadTestConsts::IMAGE_DETAILS['jpg']['width']);

})->with('models');

it('will calculate image height correctly', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $model = save($model, jpg());

    expect($model->main_file->meta('height'))
        ->toBe(LaruploadTestConsts::IMAGE_DETAILS['jpg']['height']);

})->with('models');

it('will calculate dimension of different styles correctly', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $model = save($model, jpg());

    $cover = urlToImage($model->main_file->url('cover'));
    $small = urlToImage($model->main_file->url('small'));
    $medium = urlToImage($model->main_file->url('medium'));
    $landscape = urlToImage($model->main_file->url('landscape'));
    $portrait = urlToImage($model->main_file->url('portrait'));
    $exact = urlToImage($model->main_file->url('exact'));
    $auto = urlToImage($model->main_file->url('auto'));


    expect($cover->getSize()->getWidth())->toBe(500)
        ->and($cover->getSize()->getHeight())->toBe(500)
        ->and($small->getSize()->getWidth())->toBe(200)
        ->and($small->getSize()->getHeight())->toBe(200)
        ->and($medium->getSize()->getWidth())->toBe(800)
        ->and($medium->getSize()->getHeight())->toBe(588)
        ->and($landscape->getSize()->getWidth())->toBe(400)
        ->and($landscape->getSize()->getHeight())->toBe(294)
        ->and($portrait->getSize()->getWidth())->toBe(545)
        ->and($portrait->getSize()->getHeight())->toBe(400)
        ->and($exact->getSize()->getWidth())->toBe(300)
        ->and($exact->getSize()->getHeight())->toBe(190)
        ->and($auto->getSize()->getWidth())->toBe(300)
        ->and($auto->getSize()->getHeight())->toBe(220);
})->with('models');

it('will calculate image width correctly in standalone mode', function() {
    $upload = Larupload::init('uploader')->upload(jpg());

    expect($upload->meta->width)
        ->toBe(LaruploadTestConsts::IMAGE_DETAILS['jpg']['width']);
});

it('will calculate image height correctly in standalone mode', function() {
    $upload = Larupload::init('uploader')->upload(jpg());

    expect($upload->meta->height)
        ->toBe(LaruploadTestConsts::IMAGE_DETAILS['jpg']['height']);
});

it('will calculate dimension of different styles correctly in standalone mode', function() {
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
        //
        ->and($small->getSize()->getWidth())->toBe(200)
        ->and($small->getSize()->getHeight())->toBe(200)
        //
        ->and($medium->getSize()->getWidth())->toBe(800)
        ->and($medium->getSize()->getHeight())->toBe(588)
        //
        ->and($landscape->getSize()->getWidth())->toBe(400)
        ->and($landscape->getSize()->getHeight())->toBe(294)
        //
        ->and($portrait->getSize()->getWidth())->toBe(545)
        ->and($portrait->getSize()->getHeight())->toBe(400)
        //
        ->and($exact->getSize()->getWidth())->toBe(300)
        ->and($exact->getSize()->getHeight())->toBe(190)
        //
        ->and($auto->getSize()->getWidth())->toBe(300)
        ->and($auto->getSize()->getHeight())->toBe(220);
});
