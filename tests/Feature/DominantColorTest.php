<?php

use Mostafaznv\Larupload\Enums\LaruploadImageLibrary;
use Mostafaznv\Larupload\Larupload;
use Mostafaznv\Larupload\Test\Support\LaruploadTestConsts;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadHeavyTestModel;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadLightTestModel;


it('will calculate dominant color correctly [jpg]', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $model = save($model, jpg());

    $dominantColor = LaruploadTestConsts::IMAGE_DETAILS['jpg']['color'];
    $fileColor = $model->attachment('main_file')->meta('dominant_color');

    expect($fileColor)->toBe($dominantColor);

})->with('models with dominant color');

it('will calculate dominant color correctly [png]', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $model = save($model, png());

    $dominantColor = LaruploadTestConsts::IMAGE_DETAILS['png']['color'];
    $fileColor = $model->attachment('main_file')->meta('dominant_color');

    expect($fileColor)->toBe($dominantColor);

})->with('models with dominant color');

it('will calculate dominant color correctly [webp]', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $model = save($model, webp());

    $dominantColor = LaruploadTestConsts::IMAGE_DETAILS['webp']['color'];
    $fileColor = $model->attachment('main_file')->meta('dominant_color');

    expect($fileColor)->toBe($dominantColor);

})->with('models with dominant color');

it('will calculate dominant color correctly [svg]', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $this->app['config']->set('larupload.image-processing-library', LaruploadImageLibrary::IMAGICK);

    $model = $model->newModelInstance();
    $model->withDominantColor();

    $model = save($model, svg());

    $fileColor = $model->attachment('main_file')->meta('dominant_color');

    expect($fileColor)->toMatch(LaruploadTestConsts::HEX_REGEX);

})->with('models with dominant color');

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

it('will calculate dominant color with high quality', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $model = $model->newModelInstance();
    $model->withDominantColor(quality: 1);

    $model = save($model, webp());

    $fileColor = $model->attachment('main_file')->meta('dominant_color');

    expect($fileColor)->toBe('#f6c009');

})->with('models with dominant color');

it('wont calculate dominant color if it is disabled', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $model = $model->newModelInstance();
    $model->withDominantColor(false);

    $model = save($model, jpg());

    $fileColor = $model->attachment('main_file')->meta('dominant_color');

    expect($fileColor)->toBeNull();

})->with('models with dominant color');

it('wont crash if dominant color calculation fails', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $model = $model->newModelInstance();
    $model->withDominantColor(quality: -1);

    $model = save($model, jpg());

    $fileColor = $model->attachment('main_file')->meta('dominant_color');

    expect($fileColor)->toBeNull();

})->with('models with dominant color');
