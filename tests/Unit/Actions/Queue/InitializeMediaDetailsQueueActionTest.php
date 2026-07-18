<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Bus;
use Mostafaznv\Larupload\Actions\Queue\InitializeMediaDetailsQueueAction;
use Mostafaznv\Larupload\DTOs\Style\Output;
use Mostafaznv\Larupload\Jobs\ProcessMediaDetails;
use Mostafaznv\Larupload\Larupload;
use Mostafaznv\Larupload\Storage\Attachment;
use Mostafaznv\Larupload\Test\Support\Enums\LaruploadTestModels;


beforeEach(function () {
    $this->disk = 'public';

    Bus::fake();
    Queue::fake();
    Storage::fake($this->disk);
    Storage::fake('s3');

    $this->attachment = Attachment::make('test_name');
    $this->attachment->id = 'test-id';
    $this->attachment->folder = 'test-folder';
    $this->attachment->nameKebab = 'test-name';
    $this->attachment->disk = $this->disk;
    $this->attachment->localDisk = $this->disk;
    $this->attachment->output = Output::make(
        name: 'video.mp4',
    );

    $this->table = DB::table(Larupload::DETAILS_QUEUE_TABLE);

    $this->action = resolve(InitializeMediaDetailsQueueAction::class);
});


it('dispatches media details job', function () {
    # before
    Bus::assertNotDispatched(ProcessMediaDetails::class);


    # action
    $model = LaruploadTestModels::HEAVY->instance()->getMorphClass();
    ($this->action)($this->attachment, 54, $model);


    # test
    Bus::assertDispatched(ProcessMediaDetails::class);
});

it('stores a record for the dispatched queue', function () {
    # before
    $count = $this->table->count();
    expect($count)->toBe(0);


    # action
    $model = LaruploadTestModels::HEAVY->instance()->getMorphClass();
    ($this->action)($this->attachment, 54, $model);


    # test
    $items = $this->table->get();
    $queue = $items->first();
    $files = Storage::disk($this->attachment->disk)->allFiles();

    expect($files)
        ->toBeEmpty()
        ->and($items)
        ->toHaveCount(1)
        ->and($queue->record_id)
        ->toBe(54)
        ->and($queue->record_class)
        ->toBe($model)
        ->and($queue->status)
        ->toBe(0)
        ->and($queue->message)
        ->toBeNull();
});

it('saves file locally when disk is not local', function () {
    $this->attachment->disk = 's3';
    $this->attachment->localDisk = $this->disk;
    $this->attachment->file = mp4();


    # before
    $localFiles = Storage::disk($this->attachment->localDisk)->allFiles();
    expect($localFiles)->toBeEmpty();


    # action
    $model = LaruploadTestModels::HEAVY->instance()->getMorphClass();
    ($this->action)($this->attachment, 54, $model);


    # test
    $localFiles = Storage::disk($this->attachment->localDisk)->allFiles();
    $remoteFiles = Storage::disk($this->attachment->disk)->allFiles();
    $original = larupload_relative_path($this->attachment, 'test-id', Larupload::ORIGINAL_FOLDER);
    $items = $this->table->get();
    $queue = $items->first();

    expect($remoteFiles)
        ->toBeEmpty()
        ->and($localFiles)
        ->toHaveCount(1)
        ->toBe([
            "$original/video.mp4",
        ])
        ->and($items)
        ->toHaveCount(1)
        ->and($queue->record_id)
        ->toBe(54)
        ->and($queue->record_class)
        ->toBe($model)
        ->and($queue->status)
        ->toBe(0)
        ->and($queue->message)
        ->toBeNull();
});

it('doesnt save a local copy when disk is local', function () {
    $this->attachment->disk = 'public';
    $this->attachment->localDisk = $this->disk;
    $this->attachment->file = mp4();


    # before
    $localFiles = Storage::disk($this->attachment->localDisk)->allFiles();
    expect($localFiles)->toBeEmpty();


    # action
    $model = LaruploadTestModels::HEAVY->instance()->getMorphClass();
    ($this->action)($this->attachment, 54, $model);


    # test
    $localFiles = Storage::disk($this->attachment->localDisk)->allFiles();
    $remoteFiles = Storage::disk($this->attachment->disk)->allFiles();

    expect($remoteFiles)
        ->toBeEmpty()
        ->and($localFiles)
        ->toBeEmpty();
});

