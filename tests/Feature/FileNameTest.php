<?php

use Illuminate\Support\Carbon;
use Mostafaznv\Larupload\Enums\LaruploadNamingMethod;
use Mostafaznv\Larupload\Larupload;
use Mostafaznv\Larupload\Test\Support\LaruploadTestConsts;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadHeavyTestModel;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadLightTestModel;
use function Spatie\PestPluginTestTime\testTime;

it('will upload file with correct filename [hash]', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $model = save($model, jpg());

    expect($model->main_file->meta('name'))
        ->toBe(LaruploadTestConsts::IMAGE_DETAILS['jpg']['name']['hash']);

})->with('models');

it('will upload file with correct filename [slug]', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $model = $model::class;
    $model = new $model;

    $model->main_file->namingMethod(LaruploadNamingMethod::SLUG);

    $model = save($model, jpg());

    expect($model->main_file->meta('name'))
        ->toContain(LaruploadTestConsts::IMAGE_DETAILS['jpg']['name']['slug']);
})->with('models');

it('will upload file with utf-8 filename correctly', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    config()->set('larupload.lang', 'fa');

    $model = $model::class;
    $model = new $model;

    $model->main_file->namingMethod(LaruploadNamingMethod::SLUG);

    $model = save($model, jpg(true));

    expect($model->main_file->meta('name'))
        ->toContain(LaruploadTestConsts::IMAGE_DETAILS['jpg-fa']['name']['slug']);
})->with('models');

it('will upload file with correct filename [time]', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $carbon = Carbon::createFromFormat('Y-m-d H:i:s', '1990-09-20 07:10:48');
    testTime()->freeze($carbon);

    $model = $model::class;
    $model = new $model;

    $model->main_file->namingMethod(LaruploadNamingMethod::TIME);

    $model = save($model, jpg());

    expect($model->main_file->meta('name'))
        ->toBe($carbon->unix() . '.jpg');
})->with('models');

it('will upload file with correct filename [hash] in standalone mode', function() {
    $upload = Larupload::init('uploader')
        ->namingMethod(LaruploadNamingMethod::HASH_FILE)
        ->upload(jpg());

    expect($upload->meta->name)
        ->toBe(LaruploadTestConsts::IMAGE_DETAILS['jpg']['name']['hash']);
});

it('will upload file with correct filename [slug] in standalone mode', function() {
    $upload = Larupload::init('uploader')
        ->namingMethod(LaruploadNamingMethod::SLUG)
        ->upload(jpg());


    expect($upload->meta->name)
        ->toContain(LaruploadTestConsts::IMAGE_DETAILS['jpg']['name']['slug']);
});

it('will upload file with utf-8 filename correctly in standalone mode', function() {
    $upload = Larupload::init('uploader')
        ->namingMethod(LaruploadNamingMethod::SLUG)
        ->lang('fa')
        ->upload(jpg(true));


    expect($upload->meta->name)
        ->toContain(LaruploadTestConsts::IMAGE_DETAILS['jpg-fa']['name']['slug']);
});
