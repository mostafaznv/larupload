<?php

use Mostafaznv\Larupload\Larupload;
use Mostafaznv\Larupload\Test\Support\Enums\LaruploadTestModels;
use Mostafaznv\Larupload\Test\Support\LaruploadTestConsts;


it('will calculate file-size correctly', function() {
    $model = LaruploadTestModels::HEAVY->instance();
    $model = save($model, jpg());

    $fileSize = LaruploadTestConsts::IMAGE_DETAILS['jpg']['size'];
    $metaSize = $model->attachment('main_file')->meta('size');

    expect($metaSize)->toBe($fileSize);
});

it('will calculate file-size correctly in standalone mode', function() {
    $upload = Larupload::init('uploader')->upload(jpg());

    expect($upload->meta->size)->toBe(
        LaruploadTestConsts::IMAGE_DETAILS['jpg']['size']
    );
});
