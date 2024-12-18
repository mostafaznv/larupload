<?php

use Mostafaznv\Larupload\Enums\LaruploadImageLibrary;
use Mostafaznv\Larupload\Larupload;
use Mostafaznv\Larupload\Test\Support\LaruploadTestConsts;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadHeavyTestModel;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadLightTestModel;

it('will calculate dominant color correctly [jpg]', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $file = jpg();

    try {
        $model = save($model, $file);

        $dominantColor = LaruploadTestConsts::IMAGE_DETAILS['jpg']['color'];
        $fileColor = $model->attachment('main_file')->meta('dominant_color');

        expect($fileColor)->toBe($dominantColor);
    }
    catch (Exception $e) {
        dd(
            $file->getRealPath(),
            file_exists($file->getRealPath()),
            $file->getSize(),
            $file->dimensions(),
            $file->getError(),
            $e->getMessage(),
        );
    }

})->with('models');

it('will calculate dominant color correctly [png]', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $model = save($model, png());

    $dominantColor = LaruploadTestConsts::IMAGE_DETAILS['png']['color'];
    $fileColor = $model->attachment('main_file')->meta('dominant_color');

    expect($fileColor)->toBe($dominantColor);

})->with('models');

it('will calculate dominant color correctly [webp]', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $model = save($model, webp());

    $dominantColor = LaruploadTestConsts::IMAGE_DETAILS['webp']['color'];
    $fileColor = $model->attachment('main_file')->meta('dominant_color');

    expect($fileColor)->toBe($dominantColor);

})->with('models');

it('will calculate dominant color correctly [svg]', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $this->app['config']->set('larupload.image-processing-library', LaruploadImageLibrary::IMAGICK);

    $model = $model::class;
    $model = save(new $model, svg());

    $fileColor = $model->attachment('main_file')->meta('dominant_color');

    expect($fileColor)->toMatch(LaruploadTestConsts::HEX_REGEX);

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

it('will calculate dominant color with high quality', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    config()->set('larupload.dominant-color-quality', 1);

    $model = $model::class;
    $model = save(new $model, webp());

    $fileColor = $model->attachment('main_file')->meta('dominant_color');

    expect($fileColor)->toBe('#f6c009');

})->with('models');

it('wont calculate dominant color if it is disabled', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    config()->set('larupload.dominant-color', false);

    $model = $model::class;
    $model = save(new $model, jpg());

    $fileColor = $model->attachment('main_file')->meta('dominant_color');

    expect($fileColor)->toBeNull();

})->with('models');

it('wont crash if dominant color calculation fails', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    config()->set('larupload.dominant-color-quality', -1);

    $model = $model::class;
    $model = save(new $model, jpg());

    $fileColor = $model->attachment('main_file')->meta('dominant_color');

    expect($fileColor)->toBeNull();

})->with('models');
