<?php

use Mostafaznv\Larupload\Enums\LaruploadImageLibrary;
use Mostafaznv\Larupload\Larupload;
use Mostafaznv\Larupload\Test\Support\LaruploadTestConsts;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadHeavyTestModel;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadLightTestModel;

it('will calculate dominant color correctly [jpg]', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $model = save($model, jpg());

    expect($model->main_file->meta('dominant_color'))
        ->toBe(LaruploadTestConsts::IMAGE_DETAILS['jpg']['color']);

})->with('models');

it('will calculate dominant color correctly [png]', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $model = save($model, png());

    expect($model->main_file->meta('dominant_color'))
        ->toBe(LaruploadTestConsts::IMAGE_DETAILS['png']['color']);

})->with('models');

it('will calculate dominant color correctly [webp]', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $model = save($model, webp());

    expect($model->main_file->meta('dominant_color'))
        ->toBe(LaruploadTestConsts::IMAGE_DETAILS['webp']['color']);

})->with('models');

it('will calculate dominant color correctly [svg]', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $this->app['config']->set('larupload.image-processing-library', LaruploadImageLibrary::IMAGICK);

    $model = $model::class;
    $model = save(new $model, svg());

    expect($model->main_file->meta('dominant_color'))
        ->toMatch(LaruploadTestConsts::HEX_REGEX);

})->with('models');

it('will calculate dominant color correctly [jpg] in standalone mode', function() {
    $upload = Larupload::init('uploader')->upload(jpg());

    expect($upload->meta->dominant_color)
        ->toBe(LaruploadTestConsts::IMAGE_DETAILS['jpg']['color']);

});

it('will calculate dominant color correctly [png] in standalone mode', function() {
    $upload = Larupload::init('uploader')->upload(png());

    expect($upload->meta->dominant_color)
        ->toBe(LaruploadTestConsts::IMAGE_DETAILS['png']['color']);

});

it('will calculate dominant color correctly [svg] in standalone mode', function() {
    $upload = Larupload::init('uploader')
        ->imageProcessingLibrary(LaruploadImageLibrary::IMAGICK)
        ->upload(svg());

    expect($upload->meta->dominant_color)
        ->toMatch(LaruploadTestConsts::HEX_REGEX);

});
