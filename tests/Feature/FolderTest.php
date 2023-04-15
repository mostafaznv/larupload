<?php

use Mostafaznv\Larupload\Larupload;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadHeavyTestModel;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadLightTestModel;
use Mostafaznv\Larupload\Test\Support\TestAttachmentBuilder;

it('will create folder with kebab-case convention', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $model->setAttachments(
        TestAttachmentBuilder::make($model->mode)->withSmallSizeImage()->toArray()
    );

    $model = save($model, jpg());
    $attachment = $model->attachment('main_file');

    expect($attachment->url())
        ->toContain('/main-file/')
        ->and($attachment->url('cover'))
        ->toContain('/main-file/')
        ->toContain('/cover/')
        ->and($attachment->url('small_size'))
        ->toContain('/small-size/');

})->with('models');

it('will create folder with kebab-case convention in standalone mode', function() {
    $upload = Larupload::init('uploader')
        ->image('small_size', 200, 200)
        ->upload(jpg());

    expect($upload->small_size)->toContain('/small-size/');
});
