<?php

use Mostafaznv\Larupload\Larupload;
use Mostafaznv\Larupload\Test\Support\LaruploadTestConsts;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadHeavyTestModel;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadLightTestModel;

it('will calculate file-size correctly', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $model = save($model, jpg());

    $fileSize = LaruploadTestConsts::IMAGE_DETAILS['jpg']['size'];
    $metaSize = $model->attachment('main_file')->meta('size');

    expect($metaSize)->toBe($fileSize);

})->with('models');

it('will calculate file-size correctly in standalone mode', function() {
    $upload = Larupload::init('uploader')->upload(jpg());

    expect($upload->meta->size)
        ->toBe(LaruploadTestConsts::IMAGE_DETAILS['jpg']['size']);

});
