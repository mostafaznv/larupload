<?php

use Mostafaznv\Larupload\Larupload;
use Mostafaznv\Larupload\Test\Support\LaruploadTestConsts;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadHeavyTestModel;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadLightTestModel;

$properties = [
    'original', 'cover', 'stream', 'small_size', 'small', 'medium', 'landscape', 'portrait', 'exact', 'auto', 'meta'
];

it('will return larupload object in toArray function', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) use ($properties) {
    $model = save($model, jpg());
    $array = $model->toArray();

    expect(isset($array['main_file']))
        ->toBeTrue()
        ->and($array['main_file'])
        ->toBeObject()
        ->toHaveProperties($properties)
        ->and($array['main_file']->meta)
        ->toBeObject()
        ->toHaveProperties($this->metaKeys);

})->with('models');

it('will return larupload object in toJson function', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) use ($properties) {
    $model = save($model, jpg());
    $json = json_decode($model->toJson());

    expect(isset($json->main_file))
        ->toBeTrue()
        ->and($json->main_file)
        ->toBeObject()
        ->toHaveProperty('original')
        ->toHaveProperties($properties)
        ->and($json->main_file->meta)
        ->toBeObject()
        ->toHaveProperties($this->metaKeys);

})->with('models');

it('will return meta properties camelCase', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    config()->set('larupload.camel-case-response', true);

    $model = $model::class;
    $model = save(new $model, jpg());

    $details = LaruploadTestConsts::IMAGE_DETAILS['jpg'];

    expect($model->main_file->meta())
        ->toBeObject()
        ->toBeObject()
        ->toHaveProperty('mimeType', $details['mime_type'])
        ->toHaveProperty('dominantColor', $details['color']);

})->with('models');

it('will return meta properties camelCase in standalone mode', function() {
    config()->set('larupload.camel-case-response', true);

    $upload = Larupload::init('uploader')->upload(jpg());

    $details = LaruploadTestConsts::IMAGE_DETAILS['jpg'];

    expect($upload->meta)
        ->toBeObject()
        ->toBeObject()
        ->toHaveProperty('mimeType', $details['mime_type'])
        ->toHaveProperty('dominantColor', $details['color']);

});
