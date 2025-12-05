<?php

use Illuminate\Support\Carbon;
use Mostafaznv\Larupload\Enums\LaruploadNamingMethod;
use Mostafaznv\Larupload\Larupload;
use Mostafaznv\Larupload\Test\Support\Enums\LaruploadTestModels;
use Mostafaznv\Larupload\Test\Support\LaruploadTestConsts;
use function Spatie\PestPluginTestTime\testTime;


it('will upload file with correct filename [hash]', function () {
    config()->set('larupload.naming-method', LaruploadNamingMethod::HASH_FILE);

    $model = LaruploadTestModels::HEAVY->instance();
    $model = save($model, jpg());

    $fileName = LaruploadTestConsts::IMAGE_DETAILS['jpg']['name']['hash'];
    $metaName = $model->attachment('main_file')->meta('name');
    $originalName = LaruploadTestConsts::IMAGE_DETAILS['jpg']['name']['original'];
    $metaOriginalName = $model->attachment('main_file')->meta('original_name');

    expect($metaName)
        ->toBe($fileName)
        ->and($metaOriginalName)
        ->toBe($originalName);
});

it('will upload file with correct filename [slug]', function () {
    config()->set('larupload.naming-method', LaruploadNamingMethod::SLUG);

    $model = LaruploadTestModels::LIGHT->instance();
    $model = save($model, jpg());

    $fileName = LaruploadTestConsts::IMAGE_DETAILS['jpg']['name']['slug'];
    $metaName = $model->attachment('main_file')->meta('name');
    $originalName = LaruploadTestConsts::IMAGE_DETAILS['jpg']['name']['original'];
    $metaOriginalName = $model->attachment('main_file')->meta('original_name');

    expect($metaName)
        ->toContain($fileName)
        ->and($metaOriginalName)
        ->toBe($originalName);
});

it('can store original name of uploaded file', function () {
    $model = LaruploadTestModels::HEAVY->instance();
    $model = save($model, jpg());

    $fileName = LaruploadTestConsts::IMAGE_DETAILS['jpg']['name']['original'];
    $metaOriginalName = $model->attachment('main_file')->meta('original_name');

    expect($metaOriginalName)->toContain($fileName);
});

it('will upload file with utf-8 filename correctly', function () {
    config()->set('larupload.lang', 'fa');
    config()->set('larupload.naming-method', LaruploadNamingMethod::SLUG);

    $model = LaruploadTestModels::LIGHT->instance();
    $model = save($model, jpg(true));

    $fileName = LaruploadTestConsts::IMAGE_DETAILS['jpg-fa']['name']['slug'];
    $metaName = $model->attachment('main_file')->meta('name');
    $originalName = LaruploadTestConsts::IMAGE_DETAILS['jpg-fa']['name']['original'];
    $metaOriginalName = $model->attachment('main_file')->meta('original_name');

    expect($metaName)
        ->toContain($fileName)
        ->and($metaOriginalName)
        ->toBe($originalName);
});

it('will upload file with correct filename [time]', function () {
    config()->set('larupload.naming-method', LaruploadNamingMethod::TIME);

    $carbon = Carbon::createFromFormat('Y-m-d H:i:s', '1990-09-20 07:10:48');
    testTime()->freeze($carbon);

    $model = LaruploadTestModels::HEAVY->instance();
    $model = save($model, jpg());

    $fileName = $carbon->unix() . '.jpg';
    $metaName = $model->attachment('main_file')->meta('name');
    $originalName = LaruploadTestConsts::IMAGE_DETAILS['jpg']['name']['original'];
    $metaOriginalName = $model->attachment('main_file')->meta('original_name');

    expect($metaName)
        ->toBe($fileName)
        ->and($metaOriginalName)
        ->toBe($originalName);
});

it('will upload file with correct filename [hash] in standalone mode', function () {
    $upload = Larupload::init('uploader')
        ->namingMethod(LaruploadNamingMethod::HASH_FILE)
        ->upload(jpg());

    expect($upload->meta->name)
        ->toBe(LaruploadTestConsts::IMAGE_DETAILS['jpg']['name']['hash'])
        ->and($upload->meta->original_name)
        ->toBe(LaruploadTestConsts::IMAGE_DETAILS['jpg']['name']['original']);
});

it('will upload file with correct filename [slug] in standalone mode', function () {
    $upload = Larupload::init('uploader')
        ->namingMethod(LaruploadNamingMethod::SLUG)
        ->upload(jpg());


    expect($upload->meta->name)
        ->toContain(LaruploadTestConsts::IMAGE_DETAILS['jpg']['name']['slug'])
        ->and($upload->meta->original_name)
        ->toBe(LaruploadTestConsts::IMAGE_DETAILS['jpg']['name']['original']);
});

it('can store original name of uploaded file in standalone mode', function () {
    $upload = Larupload::init('uploader')
        ->namingMethod(LaruploadNamingMethod::SLUG)
        ->upload(jpg());

    expect($upload->meta->original_name)
        ->toBe(LaruploadTestConsts::IMAGE_DETAILS['jpg']['name']['original']);
});

it('will upload file with utf-8 filename correctly in standalone mode', function () {
    $upload = Larupload::init('uploader')
        ->namingMethod(LaruploadNamingMethod::SLUG)
        ->lang('fa')
        ->upload(jpg(true));


    expect($upload->meta->name)
        ->toContain(LaruploadTestConsts::IMAGE_DETAILS['jpg-fa']['name']['slug'])
        ->and($upload->meta->original_name)
        ->toBe(LaruploadTestConsts::IMAGE_DETAILS['jpg-fa']['name']['original']);
});
