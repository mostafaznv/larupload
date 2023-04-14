<?php

use Mostafaznv\Larupload\Larupload;
use Mostafaznv\Larupload\Storage\Attachment;
use Mostafaznv\Larupload\Test\Support\Enums\LaruploadTestModels;
use Mostafaznv\Larupload\Test\Support\LaruploadTestConsts;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadHeavyTestModel;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadLightTestModel;
use Illuminate\Support\Facades\Storage;

$properties = [
    'original', 'cover', 'stream', 'small_size', 'small', 'medium', 'landscape', 'portrait', 'exact', 'auto', 'meta'
];

it('will return larupload object in toArray function', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) use ($properties) {
    $model->withAllAttachments();
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
    $model->withAllAttachments();
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

    expect($model->attachment('main_file')->meta())
        ->toBeObject()
        ->toBeObject()
        ->toHaveProperty('mimeType', $details['mime_type'])
        ->toHaveProperty('dominantColor', $details['color']);

})->with('models');

it('will return null if meta key doesnt exist', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $model = save(new $model, pdf());
    $meta = $model->attachment('main_file')->meta('doesnt-exist');

    expect($meta)->toBeNull();

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

it('will return urls of all attachments on getAttachment method', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) use ($properties) {
    $model->withAllAttachments();
    $model = save($model, pdf());
    $attachments = $model->getAttachments();

    expect($attachments)
        ->toBeObject()
        ->toHaveProperty('main_file')
        ->and($attachments->main_file)
        ->toBeObject()
        ->toHaveProperties($properties);

})->with('models');

it('will return null for cover in urls method, if generateCover property is disabled', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) use ($properties) {
    $model = save($model, jpg());
    $urls = $model->attachment('main_file')->urls();

    expect($urls->cover)->toBeTruthy();

    config()->set('larupload.generate-cover', false);

    $model = $model::find($model->id);
    $urls = $model->attachment('main_file')->urls();

    expect($urls->cover)->toBeNull();

})->with('models');

it('will return null url, if file is set and the value is LARUPLOAD_NULL', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) use ($properties) {
    $model = save($model, jpg());

    $url = $model->attachment('main_file')->url();
    expect($url)->toBeTruthy();

    $model->main_file = LARUPLOAD_NULL;
    $url = $model->attachment('main_file')->url();


    expect($url)->toBeNull();

})->with('models');

it('will return full-url for not-local storage drivers', function() {
    Storage::fake('s3');
    $baseUrl = config('filesystems.disks.s3.url');
    $attachments = [
        Attachment::make('main_file')->disk('s3')
    ];

    $model = LaruploadTestModels::HEAVY->instance();
    $model->setAttachments($attachments);

    $model = save($model, jpg());
    $url = $model->attachment('main_file')->url();

    expect($url)
        ->toStartWith($baseUrl)
        ->toEndWith('.jpg');
});

it('will return relative url for not-local storage drivers', function() {
    Storage::fake('s3');
    config()->set('filesystems.disks.s3.url', null);
    $attachments = [
        Attachment::make('main_file')->disk('s3')
    ];

    $model = LaruploadTestModels::HEAVY->instance();
    $model->setAttachments($attachments);

    $model = save($model, jpg());
    $url = $model->attachment('main_file')->url();

    expect($url)
        ->toStartWith('1/main-file/original/')
        ->toEndWith('.jpg');
});

it('will return specific attachment on getAttachment method when attachment name passed', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) use ($properties) {
    $model->withAllAttachments();
    $model = save($model, pdf());
    $attachments = $model->getAttachments('main_file');

    expect($attachments)
        ->toBeObject()
        ->not()
        ->toHaveProperty('main_file')
        ->and($attachments)
        ->toBeObject()
        ->toHaveProperties($properties);

})->with('models');

it("will return null if attachment item doesn't exist", function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $attachment = $model->attachment('not-found');
    expect($attachment)->toBeNull();

    $attachments = $model->getAttachments('not-found');
    expect($attachments)->toBeNull();

})->with('models');
