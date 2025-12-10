<?php

use Mostafaznv\Larupload\Enums\LaruploadImageLibrary;
use Mostafaznv\Larupload\Larupload;
use Mostafaznv\Larupload\Test\Support\Enums\LaruploadTestModels;
use Mostafaznv\Larupload\Test\Support\LaruploadTestConsts;


it('will calculate dominant color correctly', function () {
    $model = LaruploadTestModels::HEAVY->instance();
    $model->withDominantColor();

    $model = save($model, jpg());

    $dominantColor = LaruploadTestConsts::IMAGE_DETAILS['jpg']['color'];
    $fileColor = $model->attachment('main_file')->meta('dominant_color');

    expect($fileColor)->toBe($dominantColor);
});

it('will calculate dominant color correctly [svg]', function () {
    $this->app['config']->set('larupload.image-processing-library', LaruploadImageLibrary::IMAGICK);

    $model = LaruploadTestModels::LIGHT->instance();
    $model->withDominantColor();

    $model = save($model, svg());

    $fileColor = $model->attachment('main_file')->meta('dominant_color');

    expect($fileColor)->toMatch(LaruploadTestConsts::HEX_REGEX);
});

it('will calculate dominant color correctly [standalone]', function () {
    $upload = Larupload::init('uploader')->upload(jpg());

    expect($upload->meta->dominant_color)->toBe(
        LaruploadTestConsts::IMAGE_DETAILS['jpg']['color']
    );
});

it('will calculate dominant color with high quality', function () {
    $model = LaruploadTestModels::LIGHT->instance();
    $model->withDominantColor(true, 1);
    $model = save($model, webp());

    $fileColor = $model->attachment('main_file')->meta('dominant_color');

    expect($fileColor)->toBe('#f6c009');
});

it('wont calculate dominant color if it is disabled', function () {
    $model = LaruploadTestModels::HEAVY->instance();
    $model = save($model, jpg());

    $fileColor = $model->attachment('main_file')->meta('dominant_color');

    expect($fileColor)->toBeNull();
});

it('wont crash if dominant color calculation fails', function () {
    $model = LaruploadTestModels::LIGHT->instance();
    $model->withDominantColor(true, -1);

    $model = save($model, jpg());

    $fileColor = $model->attachment('main_file')->meta('dominant_color');

    expect($fileColor)->toBeNull();
});
