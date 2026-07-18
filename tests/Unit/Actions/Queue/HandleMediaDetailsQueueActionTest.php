<?php

use Mostafaznv\Larupload\Actions\Queue\HandleMediaDetailsQueueAction;
use Mostafaznv\Larupload\Jobs\ProcessMediaDetails;
use Mostafaznv\Larupload\Larupload;
use Mostafaznv\Larupload\Storage\Attachment;
use Mostafaznv\Larupload\Test\Support\LaruploadTestConsts;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadHeavyTestModel;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadLightTestModel;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadRemoteQueueTestModel;


beforeEach(function () {
    $this->disk = 'public';

    Queue::fake(ProcessMediaDetails::class);
    Storage::fake('s3');
    Storage::fake($this->disk);
    $this->storage = Storage::disk($this->disk);


    $this->getAttachment = function (LaruploadHeavyTestModel|LaruploadLightTestModel|LaruploadRemoteQueueTestModel $model): Attachment {
        $attachment = $model->attachment('main_file');

        $reflection = new ReflectionClass($attachment);
        $property = $reflection->getProperty('attachment');
        $property->setAccessible(true);

        return $property->getValue($attachment);
    };


    config()->set('larupload.extract-media-details-on-queue', true);
    config()->set('larupload.dominant-color', true);

    $this->action = resolve(HandleMediaDetailsQueueAction::class);
});


it('extracts video metadata and saves to model', function (LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    # prepare
    $model->attachment('main_file')->attach(mp4());
    $model->save();

    $attachment = ($this->getAttachment)($model);
    $meta = $attachment->meta();


    # test 1
    expect($meta)
        ->toHaveProperty('width', null)
        ->toHaveProperty('height', null)
        ->toHaveProperty('duration', null)
        ->toHaveProperty('dominant_color', null);

    # test 2
    $model->refresh();
    $meta = $model->attachment('main_file')->meta();

    expect($meta)
        ->toHaveProperty('width', null)
        ->toHaveProperty('height', null)
        ->toHaveProperty('duration', null)
        ->toHaveProperty('dominant_color', null);


    # action
    $this->action->execute($model, $attachment);


    # test 3
    $meta = $attachment->meta();

    expect($meta)
        ->toHaveProperty('width', LaruploadTestConsts::VIDEO_DETAILS['width'])
        ->toHaveProperty('height', LaruploadTestConsts::VIDEO_DETAILS['height'])
        ->toHaveProperty('duration', LaruploadTestConsts::VIDEO_DETAILS['duration'])
        ->toHaveProperty('dominant_color', null);

    # test 4
    $model->refresh();
    $meta = $model->attachment('main_file')->meta();

    expect($meta)
        ->toHaveProperty('width', LaruploadTestConsts::VIDEO_DETAILS['width'])
        ->toHaveProperty('height', LaruploadTestConsts::VIDEO_DETAILS['height'])
        ->toHaveProperty('duration', LaruploadTestConsts::VIDEO_DETAILS['duration'])
        ->toHaveProperty('dominant_color', null);

})->with('models');

it('extracts audio metadata and saves to model', function (LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    # prepare
    $model->attachment('main_file')->attach(mp3());
    $model->save();

    $attachment = ($this->getAttachment)($model);
    $meta = $attachment->meta();


    # test 1
    expect($meta)
        ->toHaveProperty('width', null)
        ->toHaveProperty('height', null)
        ->toHaveProperty('duration', null)
        ->toHaveProperty('dominant_color', null);

    # test 2
    $model->refresh();
    $meta = $model->attachment('main_file')->meta();

    expect($meta)
        ->toHaveProperty('width', null)
        ->toHaveProperty('height', null)
        ->toHaveProperty('duration', null)
        ->toHaveProperty('dominant_color', null);


    # action
    $this->action->execute($model, $attachment);


    # test 3
    $meta = $attachment->meta();

    expect($meta)
        ->toHaveProperty('width', null)
        ->toHaveProperty('height', null)
        ->toHaveProperty('duration', LaruploadTestConsts::AUDIO_DETAILS['duration'])
        ->toHaveProperty('dominant_color', null);

    # test 4
    $model->refresh();
    $meta = $model->attachment('main_file')->meta();

    expect($meta)
        ->toHaveProperty('width', null)
        ->toHaveProperty('height', null)
        ->toHaveProperty('duration', LaruploadTestConsts::AUDIO_DETAILS['duration'])
        ->toHaveProperty('dominant_color', null);

})->with('models');

it('extracts image metadata', function (LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    # prepare
    $model->attachment('main_file')->attach(jpg());
    $model->save();

    $attachment = ($this->getAttachment)($model);
    $meta = $attachment->meta();


    # test 1
    expect($meta)
        ->toHaveProperty('width', null)
        ->toHaveProperty('height', null)
        ->toHaveProperty('duration', null)
        ->toHaveProperty('dominant_color', null);

    # test 2
    $model->refresh();
    $meta = $model->attachment('main_file')->meta();

    expect($meta)
        ->toHaveProperty('width', null)
        ->toHaveProperty('height', null)
        ->toHaveProperty('duration', null)
        ->toHaveProperty('dominant_color', null);


    # action
    $this->action->execute($model, $attachment);


    # test 3
    $meta = $attachment->meta();

    expect($meta)
        ->toHaveProperty('width', LaruploadTestConsts::IMAGE_DETAILS['jpg']['width'])
        ->toHaveProperty('height', LaruploadTestConsts::IMAGE_DETAILS['jpg']['height'])
        ->toHaveProperty('duration', null)
        ->toHaveProperty('dominant_color', null);

    # test 4
    $model->refresh();
    $meta = $model->attachment('main_file')->meta();

    expect($meta)
        ->toHaveProperty('width', LaruploadTestConsts::IMAGE_DETAILS['jpg']['width'])
        ->toHaveProperty('height', LaruploadTestConsts::IMAGE_DETAILS['jpg']['height'])
        ->toHaveProperty('duration', null)
        ->toHaveProperty('dominant_color', null);

})->with('models');

it('extracts dominant color from image', function (LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    # prepare
    $model->withDominantColor(true, 1);
    $model->attachment('main_file')->attach(jpg());
    $model->save();

    $attachment = ($this->getAttachment)($model);
    $meta = $attachment->meta();


    # test 1
    expect($meta)
        ->toHaveProperty('width', null)
        ->toHaveProperty('height', null)
        ->toHaveProperty('duration', null)
        ->toHaveProperty('dominant_color', null);

    # test 2
    $model->refresh();
    $meta = $model->attachment('main_file')->meta();

    expect($meta)
        ->toHaveProperty('width', null)
        ->toHaveProperty('height', null)
        ->toHaveProperty('duration', null)
        ->toHaveProperty('dominant_color', null);


    # action
    $this->action->execute($model, $attachment);


    # test 3
    $meta = $attachment->meta();

    expect($meta)
        ->toHaveProperty('width', LaruploadTestConsts::IMAGE_DETAILS['jpg']['width'])
        ->toHaveProperty('height', LaruploadTestConsts::IMAGE_DETAILS['jpg']['height'])
        ->toHaveProperty('duration', null)
        ->and($meta->dominant_color)
        ->toBeTruthy()
        ->toMatch(LaruploadTestConsts::HEX_REGEX);

    # test 4
    $model->refresh();
    $meta = $model->attachment('main_file')->meta();

    expect($meta)
        ->toHaveProperty('width', LaruploadTestConsts::IMAGE_DETAILS['jpg']['width'])
        ->toHaveProperty('height', LaruploadTestConsts::IMAGE_DETAILS['jpg']['height'])
        ->toHaveProperty('duration', null)
        ->and($meta->dominant_color)
        ->toBeTruthy()
        ->toMatch(LaruploadTestConsts::HEX_REGEX);

})->with('models');

it('calls delete_local_copy after processing but since driver is local, wont delete anything', function (LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    # prepare
    $model->attachment('main_file')->attach(jpg());
    $model->save();

    $attachment = ($this->getAttachment)($model);

    $basePath = larupload_relative_path($attachment, $attachment->id, Larupload::ORIGINAL_FOLDER);
    $path = $basePath . '/' . $attachment->output->name;
    $disk = $attachment->disk;
    $driverIsLocal = disk_driver_is_local($attachment->disk);
    $md5 = md5("$disk/$path");
    $cacheKey = "larupload:$md5";

    Cache::increment($cacheKey);


    # test 1
    expect($driverIsLocal)->toBeTrue();


    # test 2
    $exists = Storage::disk($attachment->disk)->exists($path);
    expect($exists)->toBeTrue();


    # action
    $this->action->execute($model, $attachment);


    # test 3
    $exists = Storage::disk($attachment->disk)->exists($path);
    expect($exists)->toBeTrue();

})->with('models');


it('calls delete_local_copy after processing and deletes local file when the driver is not local', function () {
    # prepare
    $model = new LaruploadRemoteQueueTestModel;
    $model->attachment('main_file')->attach(jpg());
    $model->save();

    $attachment = ($this->getAttachment)($model);

    $basePath = larupload_relative_path($attachment, $attachment->id, Larupload::ORIGINAL_FOLDER);
    $path = $basePath . '/' . $attachment->output->name;
    $disk = $attachment->disk;
    $driverIsLocal = disk_driver_is_local($disk);
    $md5 = md5("$disk/$path");
    $cacheKey = "larupload:$md5";

    Cache::increment($cacheKey);


    # test 1
    expect($driverIsLocal)->toBeFalse();


    # test 2
    $exists = Storage::disk($attachment->localDisk)->exists($path);
    expect($exists)->toBeTrue();


    # action
    $this->action->execute($model, $attachment);


    # test 3
    $exists = Storage::disk($attachment->localDisk)->exists($path);
    expect($exists)->toBeFalse();

})->with('models');

it('saves video metadata to model [heavy]', function () {
    # prepare
    $model = new LaruploadHeavyTestModel;
    $model->attachment('main_file')->attach(jpg());
    $model->save();

    $attachment = ($this->getAttachment)($model);


    # test 1
    $model->refresh();

    expect($model->getAttribute('main_file_file_width'))
        ->toBeNull()
        ->and($model->getAttribute('main_file_file_height'))
        ->toBeNull();


    # action
    $this->action->execute($model, $attachment);


    # test 2
    $model->refresh();


    expect($model->getAttribute('main_file_file_width'))
        ->toBe(LaruploadTestConsts::IMAGE_DETAILS['jpg']['width'])
        ->and($model->getAttribute('main_file_file_height'))
        ->toBe(LaruploadTestConsts::IMAGE_DETAILS['jpg']['height']);

});


it('saves video metadata to model [light]', function () {
    # prepare
    $model = new LaruploadLightTestModel;
    $model->attachment('main_file')->attach(jpg());
    $model->save();

    $attachment = ($this->getAttachment)($model);


    # test 1
    $model->refresh();
    $meta = json_decode($model->getAttribute('main_file_file_meta'));

    expect($meta->width)
        ->toBeNull()
        ->and($meta->height)
        ->toBeNull();


    # action
    $this->action->execute($model, $attachment);


    # test 2
    $model->refresh();
    $meta = json_decode($model->getAttribute('main_file_file_meta'));


    expect($meta->width)
        ->toBe(LaruploadTestConsts::IMAGE_DETAILS['jpg']['width'])
        ->and($meta->height)
        ->toBe(LaruploadTestConsts::IMAGE_DETAILS['jpg']['height']);
});
