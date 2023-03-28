<?php

use Mostafaznv\Larupload\Larupload;
use Mostafaznv\Larupload\Test\Support\LaruploadTestConsts;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadHeavyTestModel;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadLightTestModel;

it('will calculate file-size correctly', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $model = save($model, jpg());

    expect($model->main_file->meta('size'))
        ->toBe(LaruploadTestConsts::IMAGE_DETAILS['jpg']['size']);

})->with('models');

it('will calculate file-size correctly in standalone mode', function() {
    $upload = Larupload::init('uploader')->upload(jpg());

    expect($upload->meta->size)
        ->toBe(LaruploadTestConsts::IMAGE_DETAILS['jpg']['size']);

});
