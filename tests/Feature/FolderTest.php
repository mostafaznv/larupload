<?php

use Mostafaznv\Larupload\Larupload;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadHeavyTestModel;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadLightTestModel;

it('will create folder with kebab-case convention', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $model = save($model, jpg());

    expect($model->main_file->url())
        ->toContain('/main-file/')
        ->and($model->main_file->url('cover'))
        ->toContain('/main-file/')
        ->toContain('/cover/')
        ->and($model->main_file->url('small_size'))
        ->toContain('/small-size/');

})->with('models');

it('will create folder with kebab-case convention in standalone mode', function() {
    $upload = Larupload::init('uploader')
        ->image('small_size', 200, 200)
        ->upload(jpg());

    expect($upload->small_size)->toContain('/small-size/');
});
