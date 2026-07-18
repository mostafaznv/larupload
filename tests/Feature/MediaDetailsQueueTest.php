<?php

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Bus;
use Mostafaznv\Larupload\Enums\LaruploadSecureIdsMethod;
use Mostafaznv\Larupload\Events\LaruploadMediaDetailsQueueFinished;
use Illuminate\Support\Facades\Storage;
use Mostafaznv\Larupload\Jobs\ProcessMediaDetails;
use Mostafaznv\Larupload\Larupload;
use Mostafaznv\Larupload\Models\LaruploadMediaDetailsQueue;
use Mostafaznv\Larupload\Test\Support\Enums\LaruploadTestModels;
use Mostafaznv\Larupload\Test\Support\LaruploadTestConsts;


beforeEach(function () {
    Storage::fake('public');
    Storage::fake('local');
    Storage::fake('s3');

    Bus::fake();
    Queue::fake();

    Event::fake(LaruploadMediaDetailsQueueFinished::class);

    config()->set('larupload.extract-media-details-on-queue', true);
});


it('can extract media details on queue', function (UploadedFile $file) {
    Bus::assertNotDispatched(ProcessMediaDetails::class);

    save(LaruploadTestModels::QUEUE->instance(), $file);

    Bus::assertDispatched(ProcessMediaDetails::class);

})->with([
    'mp4' => fn() => mp4(),
    'mp3' => fn() => mp3(),
    'jpg' => fn() => jpg(),
]);

it('wont extract media details on queue in standalone mode', function (UploadedFile $file) {
    Bus::assertNotDispatched(ProcessMediaDetails::class);

    Larupload::init('uploader')->upload($file);

    Bus::assertNotDispatched(ProcessMediaDetails::class);

})->with([
    'mp4' => fn() => mp4(),
    'mp3' => fn() => mp3(),
    'jpg' => fn() => jpg(),
]);

it('stores data into database after calculating media details on queue', function () {
    $model = LaruploadTestModels::HEAVY->instance();
    $model = save($model, jpg());

    # test 1
    $model = $model->fresh();
    $meta = $model->attachment('main_file')->meta();

    expect($meta->width)
        ->toBeNull()
        ->and($meta->height)
        ->toBeNull();


    # queue
    $queue = DB::table(Larupload::DETAILS_QUEUE_TABLE)->first();
    $job = new ProcessMediaDetails($queue->id, $model->id, 'main_file', $model::class);
    $job->handle();


    # test 2
    $model = $model->fresh();
    $meta = $model->attachment('main_file')->meta();

    expect($meta->width)
        ->toBe(LaruploadTestConsts::IMAGE_DETAILS['jpg']['width'])
        ->and($meta->height)
        ->toBe(LaruploadTestConsts::IMAGE_DETAILS['jpg']['height']);
});

it('queues extraction process for remote disks and deletes local files after finishing the process', function () {
    # init
    $disk = 's3';
    $localDisk = config()->get('larupload.local-disk');

    # save model
    $model = LaruploadTestModels::REMOTE_QUEUE->instance();
    $model = save($model, jpg());


    # prepare for assertions
    $fileName = $model->attachment('main_file')->meta('name');
    $s3Files = Storage::disk($disk)->allFiles();
    $localFiles = Storage::disk($localDisk)->allFiles();


    $model = $model->fresh();
    $meta = $model->attachment('main_file')->meta();


    # test
    expect($s3Files)
        ->toHaveCount(2)
        ->and($localFiles)
        ->toHaveCount(1)
        ->and($localFiles[0])
        ->toEndWith($fileName)
        ->and($meta->width)
        ->toBeNull()
        ->and($meta->height)
        ->toBeNull();;


    # queue
    $queue = DB::table(Larupload::DETAILS_QUEUE_TABLE)->first();
    $job = new ProcessMediaDetails($queue->id, $model->id, 'main_file', $model::class);
    $job->handle();


    # test
    $s3Files = Storage::disk($disk)->allFiles();
    $localFiles = Storage::disk($localDisk)->allFiles();
    $model = $model->fresh();
    $meta = $model->attachment('main_file')->meta();

    expect($s3Files)
        ->toHaveCount(2)
        ->and($localFiles)
        ->toHaveCount(0)
        ->and($meta->width)
        ->toBe(LaruploadTestConsts::IMAGE_DETAILS['jpg']['width'])
        ->and($meta->height)
        ->toBe(LaruploadTestConsts::IMAGE_DETAILS['jpg']['height']);
});

it('can queue extraction process when using secure-ids', function () {
    config()->set('larupload.secure-ids', LaruploadSecureIdsMethod::ULID);

    $model = LaruploadTestModels::HEAVY->instance();
    $model = save($model, jpg());

    # test 1
    $model = $model->fresh();
    $meta = $model->attachment('main_file')->meta();

    expect($meta->width)
        ->toBeNull()
        ->and($meta->height)
        ->toBeNull();


    # queue
    $queue = DB::table(Larupload::DETAILS_QUEUE_TABLE)->first();
    $job = new ProcessMediaDetails($queue->id, $model->id, 'main_file', $model::class);
    $job->handle();


    # test 2
    $model = $model->fresh();
    $meta = $model->attachment('main_file')->meta();

    expect($meta->width)
        ->toBe(LaruploadTestConsts::IMAGE_DETAILS['jpg']['width'])
        ->and($meta->height)
        ->toBe(LaruploadTestConsts::IMAGE_DETAILS['jpg']['height']);
});


it('will change queue status after processing queue', function () {
    $model = LaruploadTestModels::HEAVY->instance();
    $model = save($model, jpg());


    # test 1
    $queue = DB::table(Larupload::DETAILS_QUEUE_TABLE)->first();
    expect($queue->status)->toBe(0);


    # queue
    $queue = DB::table(Larupload::DETAILS_QUEUE_TABLE)->first();
    $job = new ProcessMediaDetails($queue->id, $model->id, 'main_file', $model::class);
    $job->handle();


    # test 2
    $queue = DB::table(Larupload::DETAILS_QUEUE_TABLE)->first();

    expect($queue->status)->toBe(1);

});

it('will fire an event when process is finished', function () {
    $model = LaruploadTestModels::HEAVY->instance();
    $model = save($model, jpg());


    # test 1
    Event::assertNotDispatched(LaruploadMediaDetailsQueueFinished::class);

    # queue
    $queue = DB::table(Larupload::DETAILS_QUEUE_TABLE)->first();
    $job = new ProcessMediaDetails($queue->id, $model->id, 'main_file', $model::class);
    $job->handle();


    # test 2
    Event::assertDispatched(LaruploadMediaDetailsQueueFinished::class);

});

it('will update queue record with error message, when process failed', function () {
    $model = LaruploadTestModels::HEAVY->instance();
    $model = save($model, jpg());
    $queue = DB::table(Larupload::DETAILS_QUEUE_TABLE)->first();

    # test 1
    expect($queue->message)->toBeNull();


    $path = Storage::disk('local')->path('/');
    rmRf($path);


    # queue
    try {
        $queue = DB::table(Larupload::DETAILS_QUEUE_TABLE)->first();
        $job = new ProcessMediaDetails($queue->id, $model->id, 'main_file', $model::class);
        $job->handle();

        expect(true)->toBeFalse();
    }
    catch (Exception $e) {
        $message = $e->getMessage();

        expect($message)->toStartWith('File not found on disk: local, path: upload-heavy');

        $queue = DB::table(Larupload::DETAILS_QUEUE_TABLE)->first();
        expect($queue->message)->toBe($message);
    }
});

it('can load queue relationships of model', function () {
    $model = LaruploadTestModels::HEAVY->instance();
    $model->load('laruploadMediaDetailsQueue', 'laruploadMediaDetailsQueues');
    $model = save($model, jpg());

    expect($model->laruploadMediaDetailsQueue)
        ->toBeInstanceOf(LaruploadMediaDetailsQueue::class)
        ->id->toBe(1)
        ->status->toBe(0)
        ->message->toBeNull()
        ->and($model->laruploadMediaDetailsQueues)
        ->toBeInstanceOf(Collection::class)
        ->toHaveCount(1)
        ->and($model->laruploadMediaDetailsQueues[0])
        ->toBeInstanceOf(LaruploadMediaDetailsQueue::class);
});
