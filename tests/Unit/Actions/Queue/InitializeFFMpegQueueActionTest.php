<?php

use FFMpeg\Format\Audio\Wav;
use FFMpeg\Format\Video\X264;
use Mostafaznv\Larupload\Actions\Queue\HandleFFMpegQueueAction;
use Mostafaznv\Larupload\Actions\Queue\InitializeFFMpegQueueAction;
use Mostafaznv\Larupload\DTOs\Style\Output;
use Mostafaznv\Larupload\Enums\LaruploadMode;
use Mostafaznv\Larupload\Exceptions\FFMpegQueueMaxNumExceededException;
use Mostafaznv\Larupload\Larupload;
use Mostafaznv\Larupload\Storage\Attachment;
use Mostafaznv\Larupload\Test\Support\Enums\LaruploadTestModels;


beforeEach(function () {
    $this->disk = 'public';

    Queue::fake();
    Storage::fake($this->disk);
    Storage::fake('s3');

    $this->attachment = Attachment::make('test_name');
    $this->attachment->id = 'test-id';
    $this->attachment->folder = 'test-folder';
    $this->attachment->nameKebab = 'test-name';
    $this->attachment->disk = $this->disk;
    $this->attachment->localDisk = $this->disk;
    $this->attachment->ffmpegMaxQueueNum = 0;
    $this->attachment->output = Output::make(
        name: 'video.mp4',
    );

    $this->table = DB::table(Larupload::FFMPEG_QUEUE_TABLE);

    $this->action = resolve(InitializeFFMpegQueueAction::class);
});


it('dispatches ffmpeg job when queue limit is not exceeded [model]', function () {
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

it('dispatches ffmpeg job when queue limit is not exceeded [standalone]', function () {
    # before
    $count = $this->table->count();
    expect($count)->toBe(0);


    # action
    ($this->action)($this->attachment, 42, Larupload::class, true);


    # test
    $items = $this->table->get();
    $queue = $items->first();
    $files = Storage::disk($this->attachment->disk)->allFiles();

    expect($files)
        ->toBeEmpty()
        ->and($items)
        ->toHaveCount(1)
        ->and($queue->record_id)
        ->toBe(42)
        ->and($queue->record_class)
        ->toBe(Larupload::class)
        ->and($queue->status)
        ->toBe(0)
        ->and($queue->message)
        ->toBeNull();
});

it('saves file locally when disk is not local', function () {
    $this->attachment->disk = 's3';
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
    $original = larupload_relative_path($this->attachment, 54, Larupload::ORIGINAL_FOLDER);


    expect($remoteFiles)
        ->toBeEmpty()
        ->and($localFiles)
        ->toHaveCount(1)
        ->toBe([
            "$original/video.mp4",
        ]);
});

it('throws exception when ffmpeg queue limit is exceeded', function () {
    $this->attachment->ffmpegMaxQueueNum = 1;
    $model = LaruploadTestModels::HEAVY->instance()->getMorphClass();

    # works
    ($this->action)($this->attachment, 54, $model);

    $items = $this->table->get();
    expect($items)->toHaveCount(1);


    # does not work
    try {
        ($this->action)($this->attachment, 23, $model);

        expect(true)->toBeFalse();
    }
    catch (FFMpegQueueMaxNumExceededException $e) {
        expect($e->getMessage())->toBe(
            trans('larupload::messages.max-queue-num-exceeded')
        );
    }
});

it('wont throw an exception limit is zero', function () {
    $this->attachment->ffmpegMaxQueueNum = 0;
    $model = LaruploadTestModels::HEAVY->instance()->getMorphClass();

    # works
    ($this->action)($this->attachment, 54, $model);

    $items = $this->table->get();
    expect($items)->toHaveCount(1);


    # works
    ($this->action)($this->attachment, 32, $model);

    $items = $this->table->get();
    expect($items)->toHaveCount(2);
});


