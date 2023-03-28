<?php

use Mostafaznv\Larupload\Test\Support\Models\LaruploadHeavyTestModel;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadLightTestModel;
use Symfony\Component\HttpFoundation\StreamedResponse;


it('will download original file', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $model = save($model, jpg());

    expect($model->main_file->download())
        ->toBeInstanceOf(StreamedResponse::class)
        ->getStatusCode()
        ->toBe(200);

})->with('models');

it('will download cover file', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $model = save($model, jpg());

    expect($model->main_file->download('cover'))
        ->toBeInstanceOf(StreamedResponse::class)
        ->getStatusCode()
        ->toBe(200);

})->with('models');

it('will download custom styles', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $model = save($model, jpg());

    expect($model->main_file->download('landscape'))
        ->toBeInstanceOf(StreamedResponse::class)
        ->getStatusCode()
        ->toBe(200);

})->with('models');

it('will return null for styles that do not exist', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $model = save($model, jpg());

    expect($model->main_file->download('not-exists'))
        ->toBeNull();

})->with('models');
