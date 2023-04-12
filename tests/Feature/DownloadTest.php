<?php

use Mostafaznv\Larupload\Enums\LaruploadSecureIdsMethod;
use Mostafaznv\Larupload\Storage\Attachment;
use Mostafaznv\Larupload\Test\Support\Enums\LaruploadTestModels;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadHeavyTestModel;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadLightTestModel;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Http\RedirectResponse;


it('will download original file', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $model = save($model, jpg());
    $attachment = $model->attachment('main_file');

    expect($attachment->download())
        ->toBeInstanceOf(StreamedResponse::class)
        ->getStatusCode()
        ->toBe(200);

})->with('models');

it('will download cover file', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $model = save($model, jpg());
    $attachment = $model->attachment('main_file');

    expect($attachment->download('cover'))
        ->toBeInstanceOf(StreamedResponse::class)
        ->getStatusCode()
        ->toBe(200);

})->with('models');

it('will download custom styles', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $model = save($model, jpg());
    $attachment = $model->attachment('main_file');

    expect($attachment->download('landscape'))
        ->toBeInstanceOf(StreamedResponse::class)
        ->getStatusCode()
        ->toBe(200);

})->with('models');

it('will return null for styles that do not exist', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $model = save($model, jpg());
    $attachment = $model->attachment('main_file');

    expect($attachment->download('not-exists'))
        ->toBeNull();

})->with('models');

it('will download original file when secure-ids is enabled', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    config()->set('larupload.secure-ids', LaruploadSecureIdsMethod::ULID);

    $model = $model::class;
    $model = save(new $model, jpg());
    $attachment = $model->attachment('main_file');

    expect($attachment->download())
        ->toBeInstanceOf(StreamedResponse::class)
        ->getStatusCode()
        ->toBe(200);

})->with('models');

it('will return null download response, if file is set and the value is LARUPLOAD_NULL', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $model = save($model, jpg());
    $attachment = $model->attachment('main_file');

    expect($attachment->download())
        ->getStatusCode()
        ->toBe(200);

    $model->main_file = LARUPLOAD_NULL;
    $attachment = $model->attachment('main_file');

    expect($attachment->download())->toBeNull();

})->with('models');

it('will download files when storage driver is not local', function() {
    Storage::fake('s3');
    $attachments = [
        Attachment::make('main_file')->disk('s3')
    ];

    $model = LaruploadTestModels::HEAVY->instance();
    $model->setAttachments($attachments);

    $model = save($model, jpg());
    $attachment = $model->attachment('main_file');

    expect($attachment->download())
        ->toBeInstanceOf(RedirectResponse::class)
        ->getStatusCode()
        ->toBe(302);
});

it('wont download files when storage driver is not local but baseurl is not set', function() {
    Storage::fake('s3');
    config()->set('filesystems.disks.s3.url', null);
    $attachments = [
        Attachment::make('main_file')->disk('s3')
    ];

    $model = LaruploadTestModels::HEAVY->instance();
    $model->setAttachments($attachments);

    $model = save($model, jpg());
    $attachment = $model->attachment('main_file');

    expect($attachment->download())->toBeNull();
});
