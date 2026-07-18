<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Mostafaznv\Larupload\Events\LaruploadMediaDetailsQueueFinished;
use Mostafaznv\Larupload\Jobs\ProcessMediaDetails;
use Mostafaznv\Larupload\Larupload;
use Mostafaznv\Larupload\Test\Support\Enums\LaruploadTestModels;
use Mostafaznv\Larupload\Test\Support\LaruploadTestConsts;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadHeavyTestModel;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadLightTestModel;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadQueueTestModel;


beforeEach(function () {
    Bus::fake();
    Queue::fake();
    Event::fake(LaruploadMediaDetailsQueueFinished::class);

    Storage::fake('public');
    Storage::fake('s3');

    config()->set('larupload.extract-media-details-on-queue', true);
});


it('will queue media details processing after saving a model', function (LaruploadHeavyTestModel|LaruploadLightTestModel $model, UploadedFile $file) {
    Bus::assertNotDispatched(ProcessMediaDetails::class);

    save(LaruploadTestModels::HEAVY->instance(), $file);

    Bus::assertDispatched(ProcessMediaDetails::class);

})->with([
    'video' => fn() => [LaruploadTestModels::HEAVY->instance(), mp4()],
    'audio' => fn() => [LaruploadTestModels::LIGHT->instance(), mp3()],
    'image' => fn() => [LaruploadTestModels::HEAVY->instance(), jpg()],
]);

it('will save video details through queue process', function () {
    $model = save(LaruploadTestModels::HEAVY->instance(), mp4());
    $queue = DB::table(Larupload::DETAILS_QUEUE_TABLE)->first();


    # test 1
    expect($queue->record_id)
        ->toBe($model->id)
        ->and($queue->record_class)
        ->toBe($model::class)
        ->and($queue->status)
        ->toBe(0)
        ->and($queue->message)
        ->toBeNull();


    # test 2
    $meta = $model->attachment('main_file')->meta();
    expect($meta->width)
        ->toBeNull()
        ->and($meta->width)
        ->toBeNull()
        ->and($meta->duration)
        ->toBeNull()
        ->and($meta->dominant_color)
        ->toBeNull();


    $process = new ProcessMediaDetails($queue->id, $model->id, 'main_file', $model::class);
    $process->handle();


    # test 3
    $queue = DB::table(Larupload::DETAILS_QUEUE_TABLE)->first();

    expect($queue->record_id)
        ->toBe($model->id)
        ->and($queue->record_class)
        ->toBe($model::class)
        ->and($queue->status)
        ->toBe(1)
        ->and($queue->message)
        ->toBeNull();


    # test 4
    $model = LaruploadTestModels::HEAVY->instance()->find($model->id);
    $meta = $model->attachment('main_file')->meta();

    expect($meta->width)
        ->toBe(LaruploadTestConsts::VIDEO_DETAILS['width'])
        ->and($meta->height)
        ->toBe(LaruploadTestConsts::VIDEO_DETAILS['height'])
        ->and($meta->duration)
        ->toBe(LaruploadTestConsts::VIDEO_DETAILS['duration'])
        ->and($meta->dominant_color)
        ->toBeNull();
});

it('will save audio details through queue process', function () {
    $model = save(LaruploadTestModels::LIGHT->instance(), mp3());
    $queue = DB::table(Larupload::DETAILS_QUEUE_TABLE)->first();


    # test 1
    expect($queue->record_id)
        ->toBe($model->id)
        ->and($queue->record_class)
        ->toBe($model::class)
        ->and($queue->status)
        ->toBe(0)
        ->and($queue->message)
        ->toBeNull();


    # test 2
    $meta = $model->attachment('main_file')->meta();
    expect($meta->width)
        ->toBeNull()
        ->and($meta->width)
        ->toBeNull()
        ->and($meta->duration)
        ->toBeNull()
        ->and($meta->dominant_color)
        ->toBeNull();


    $process = new ProcessMediaDetails($queue->id, $model->id, 'main_file', $model::class);
    $process->handle();


    # test 3
    $queue = DB::table(Larupload::DETAILS_QUEUE_TABLE)->first();

    expect($queue->record_id)
        ->toBe($model->id)
        ->and($queue->record_class)
        ->toBe($model::class)
        ->and($queue->status)
        ->toBe(1)
        ->and($queue->message)
        ->toBeNull();


    # test 4
    $model = LaruploadTestModels::LIGHT->instance()->find($model->id);
    $meta = $model->attachment('main_file')->meta();

    expect($meta->width)
        ->toBeNull()
        ->and($meta->height)
        ->toBeNull()
        ->and($meta->duration)
        ->toBe(LaruploadTestConsts::AUDIO_DETAILS['duration'])
        ->and($meta->dominant_color)
        ->toBeNull();
});

it('will save image details through queue process', function () {
    $model = save(LaruploadTestModels::HEAVY->instance(), jpg());
    $queue = DB::table(Larupload::DETAILS_QUEUE_TABLE)->first();


    # test 1
    expect($queue->record_id)
        ->toBe($model->id)
        ->and($queue->record_class)
        ->toBe($model::class)
        ->and($queue->status)
        ->toBe(0)
        ->and($queue->message)
        ->toBeNull();


    # test 2
    $meta = $model->attachment('main_file')->meta();
    expect($meta->width)
        ->toBeNull()
        ->and($meta->width)
        ->toBeNull()
        ->and($meta->duration)
        ->toBeNull()
        ->and($meta->dominant_color)
        ->toBeNull();


    $process = new ProcessMediaDetails($queue->id, $model->id, 'main_file', $model::class);
    $process->handle();


    # test 3
    $queue = DB::table(Larupload::DETAILS_QUEUE_TABLE)->first();

    expect($queue->record_id)
        ->toBe($model->id)
        ->and($queue->record_class)
        ->toBe($model::class)
        ->and($queue->status)
        ->toBe(1)
        ->and($queue->message)
        ->toBeNull();


    # test 4
    $model = LaruploadTestModels::HEAVY->instance()->find($model->id);
    $meta = $model->attachment('main_file')->meta();

    expect($meta->width)
        ->toBe(LaruploadTestConsts::IMAGE_DETAILS['jpg']['width'])
        ->and($meta->height)
        ->toBe(LaruploadTestConsts::IMAGE_DETAILS['jpg']['height'])
        ->and($meta->duration)
        ->toBeNull()
        ->and($meta->dominant_color)
        ->toBeNull();
});

it('will change queue status after processing queue', function () {
    $model = save(LaruploadTestModels::QUEUE->instance(), jpg());
    $queue = DB::table(Larupload::DETAILS_QUEUE_TABLE)->first();

    expect($queue)
        ->toBeObject()
        ->and($queue->status)
        ->toBe(0);

    $process = new ProcessMediaDetails($queue->id, $model->id, 'main_file', $model::class);
    $process->handle();

    $queue = DB::table(Larupload::DETAILS_QUEUE_TABLE)->first();

    expect($queue)
        ->toBeObject()
        ->and($queue->status)
        ->toBe(1);

});

it('will fire an event when process is finished', function () {
    $model = save(LaruploadTestModels::QUEUE->instance(), jpg());
    $queue = DB::table(Larupload::DETAILS_QUEUE_TABLE)->first();

    Event::assertNotDispatched(LaruploadMediaDetailsQueueFinished::class);

    $process = new ProcessMediaDetails($queue->id, $model->id, 'main_file', $model::class);
    $process->handle();

    Event::assertDispatched(LaruploadMediaDetailsQueueFinished::class);

});


it('will update queue record with error message, when process failed', function () {
    $model = save(LaruploadTestModels::QUEUE->instance(), jpg());
    $queue = DB::table(Larupload::DETAILS_QUEUE_TABLE)->first();

    expect($queue->message)->toBeNull();

    LaruploadQueueTestModel::where('id', $model->id)->delete();

    try {
        $process = new ProcessMediaDetails($queue->id, $model->id, 'main_file', $model::class);
        $process->handle();
    }
    catch (Exception $e) {
        $message = 'Unable to process media details: the model or attached file could not be found.';
        $queue = DB::table(Larupload::DETAILS_QUEUE_TABLE)->first();

        expect($e->getMessage())
            ->toBe($message)
            ->and($queue->status)
            ->toBe(0)
            ->and($queue->message)
            ->toBe($message);
    }
});
