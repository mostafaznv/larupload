<?php

use FFMpeg\Format\Audio\Mp3;
use FFMpeg\Format\Audio\Wav;
use FFMpeg\Format\Video\X264;
use Mostafaznv\Larupload\Actions\HandleStylesAction;
use Mostafaznv\Larupload\DTOs\Style\Output;
use Mostafaznv\Larupload\Enums\LaruploadFileType;
use Mostafaznv\Larupload\Enums\LaruploadImageLibrary;
use Mostafaznv\Larupload\Enums\LaruploadMediaStyle;
use Mostafaznv\Larupload\Enums\LaruploadMode;
use Mostafaznv\Larupload\Exceptions\FFMpegQueueMaxNumExceededException;
use Mostafaznv\Larupload\Jobs\ProcessFFMpeg;
use Mostafaznv\Larupload\Larupload;
use Mostafaznv\Larupload\Storage\Attachment;
use Mostafaznv\Larupload\Test\Support\Enums\LaruploadTestModels;


beforeEach(function () {
    $this->disk = 'public';

    Queue::fake();
    Storage::fake('s3');
    Storage::fake($this->disk);

    $this->attachment = Attachment::make('main_file');
    $this->attachment->disk = $this->disk;
    $this->attachment->localDisk = $this->disk;
    $this->attachment->folder = 'test-folder';
    $this->attachment->nameKebab = 'main-file';
    $this->attachment->id = 'test-id';

    $this->path = larupload_relative_path($this->attachment, $this->attachment->id);
    $this->original = $this->path . '/' . Larupload::ORIGINAL_FOLDER;
    $this->cover = $this->path . '/' . Larupload::COVER_FOLDER;

    $this->model = LaruploadTestModels::HEAVY->instance();
    $this->model->id = 52;
});


# image
it('handles image styles correctly', function () {
    # prepare
    $this->attachment->file = jpg();
    $this->attachment->type = LaruploadFileType::IMAGE;
    $this->attachment->image('small', 200, 200, LaruploadMediaStyle::CROP);
    $this->attachment->image('medium', 400, null, LaruploadMediaStyle::SCALE_HEIGHT);

    $this->attachment->output = Output::make(
        name: 'image.jpg',
    );


    # before
    $files = Storage::disk($this->attachment->disk)->allFiles();
    expect($files)->toBeEmpty();


    # action
    HandleStylesAction::make($this->attachment)->run($this->attachment->id, $this->model);


    # test
    $files = Storage::disk($this->attachment->disk)->allFiles();

    expect($files)
        ->toHaveCount(2)
        ->toContain("$this->path/small/image.jpg")
        ->toContain("$this->path/medium/image.jpg");
});

it('can fix image extensions', function () {
    # prepare
    $this->attachment->file = svg();
    $this->attachment->imageProcessingLibrary = LaruploadImageLibrary::IMAGICK;
    $this->attachment->type = LaruploadFileType::IMAGE;
    $this->attachment->image('small', 200, 200, LaruploadMediaStyle::CROP);

    $this->attachment->output = Output::make(
        name: 'image.svg',
    );


    # before
    $files = Storage::disk($this->attachment->disk)->allFiles();
    expect($files)->toBeEmpty();


    # action
    HandleStylesAction::make($this->attachment)->run($this->attachment->id, $this->model);


    # test
    $files = Storage::disk($this->attachment->disk)->allFiles();

    expect($files)->toBe([
        "$this->path/small/image.jpg"
    ]);
});


# video
it('handles video styles correctly without ffmpeg queue', function () {
    # prepare
    $this->attachment->file = mp4();
    $this->attachment->type = LaruploadFileType::VIDEO;
    $this->attachment->video('small', 200, 200); # video
    $this->attachment->video('converted', format: new Mp3); # audio
    $this->attachment->stream('480p', 200, 200, new X264); # stream

    $this->attachment->output = Output::make(
        name: 'test-video.mp4',
    );


    # before
    $files = Storage::disk($this->attachment->disk)->allFiles();
    expect($files)->toBeEmpty();


    # action
    HandleStylesAction::make($this->attachment)->run($this->attachment->id, $this->model);


    # test
    $files = Storage::disk($this->attachment->disk)->allFiles();

    expect($files)
        ->toHaveCount(6)
        ->toContain("$this->path/small/test-video.mp4")
        ->toContain("$this->path/converted/test-video.mp3")
        ->toContain("$this->path/stream/test-video.m3u8")
        ->toContain("$this->path/stream/480p/480p-0.ts")
        ->toContain("$this->path/stream/480p/480p-list.m3u8")
        ->toContain("$this->path/stream/480p/master.m3u8");
});

it('handles video styles correctly with ffmpeg queue [model]', function () {
    # prepare
    $this->attachment->file = mp4();
    $this->attachment->type = LaruploadFileType::VIDEO;
    $this->attachment->video('small', 200, 200);
    $this->attachment->ffmpegQueue = true;

    $this->attachment->output = Output::make(
        name: 'test-video.mp4',
    );


    # before
    $files = Storage::disk($this->attachment->disk)->allFiles();
    expect($files)->toBeEmpty();


    # action
    HandleStylesAction::make($this->attachment)->run($this->attachment->id, $this->model);


    # test
    $files = Storage::disk($this->attachment->disk)->allFiles();
    expect($files)->toHaveCount(0);

    Queue::assertPushed(ProcessFFMpeg::class, 1);
    Queue::assertPushed(function (ProcessFFMpeg $job) {
        return $job->getId() === 52;
    });
});

it('handles video styles correctly with ffmpeg queue [standalone]', function () {
    # prepare
    $this->attachment->file = mp4();
    $this->attachment->type = LaruploadFileType::VIDEO;
    $this->attachment->video('small', 200, 200);
    $this->attachment->ffmpegQueue = true;
    $this->attachment->mode = LaruploadMode::STANDALONE;

    $this->attachment->output = Output::make(
        name: 'test-video.mp4',
    );


    # before
    $files = Storage::disk($this->attachment->disk)->allFiles();
    expect($files)->toBeEmpty();


    # action
    HandleStylesAction::make($this->attachment)->run(651, Larupload::class, true);


    # test
    $files = Storage::disk($this->attachment->disk)->allFiles();
    expect($files)->toHaveCount(0);

    Queue::assertPushed(ProcessFFMpeg::class, 1);
    Queue::assertPushed(function (ProcessFFMpeg $job) {
        return $job->getId() === 651;
    });
});

it('keeps a local copy of original video file when queue is enabled and disk is not local', function () {
    # prepare
    $this->attachment->disk = 's3';
    $this->attachment->file = mp4();
    $this->attachment->type = LaruploadFileType::VIDEO;
    $this->attachment->video('small', 200, 200);
    $this->attachment->ffmpegQueue = true;

    $this->attachment->output = Output::make(
        name: 'test-video.mp4',
    );


    # before
    $localFiles = Storage::disk($this->attachment->localDisk)->allFiles();
    $remoteFiles = Storage::disk($this->attachment->disk)->allFiles();

    expect($localFiles)
        ->toBeEmpty()
        ->and($remoteFiles)
        ->toBeEmpty();


    # action
    HandleStylesAction::make($this->attachment)->run($this->attachment->id, $this->model);


    # test
    $localFiles = Storage::disk($this->attachment->localDisk)->allFiles();
    $remoteFiles = Storage::disk($this->attachment->disk)->allFiles();
    $original = larupload_relative_path($this->attachment, $this->model->id, Larupload::ORIGINAL_FOLDER);


    expect($remoteFiles)
        ->toHaveCount(0)
        ->and($localFiles)
        ->toBe([
            "$original/test-video.mp4",
        ]);

    Queue::assertPushed(ProcessFFMpeg::class, 1);
    Queue::assertPushed(function (ProcessFFMpeg $job) {
        return $job->getId() === 52;
    });
});


# audio
it('handles audio styles correctly without ffmpeg queue', function () {
    # prepare
    $this->attachment->file = mp3();
    $this->attachment->type = LaruploadFileType::AUDIO;
    $this->attachment->audio('low', new Wav);
    $this->attachment->audio('high', new Mp3);

    $this->attachment->output = Output::make(
        name: 'test-audio.mp3',
    );


    # before
    $files = Storage::disk($this->attachment->disk)->allFiles();
    expect($files)->toBeEmpty();


    # action
    HandleStylesAction::make($this->attachment)->run($this->attachment->id, $this->model);


    # test
    $files = Storage::disk($this->attachment->disk)->allFiles();

    expect($files)
        ->toHaveCount(2)
        ->toContain("$this->path/low/test-audio.wav")
        ->toContain("$this->path/high/test-audio.mp3");
});

it('handles audio styles correctly with ffmpeg queue [model]', function () {
    # prepare
    $this->attachment->file = mp3();
    $this->attachment->type = LaruploadFileType::AUDIO;
    $this->attachment->audio('low', new Wav);
    $this->attachment->ffmpegQueue = true;

    $this->attachment->output = Output::make(
        name: 'test-audio.mp3',
    );


    # before
    $files = Storage::disk($this->attachment->disk)->allFiles();
    expect($files)->toBeEmpty();


    # action
    HandleStylesAction::make($this->attachment)->run($this->attachment->id, $this->model);


    # test
    $files = Storage::disk($this->attachment->disk)->allFiles();
    expect($files)->toHaveCount(0);

    Queue::assertPushed(ProcessFFMpeg::class, 1);
    Queue::assertPushed(function (ProcessFFMpeg $job) {
        return $job->getId() === 52;
    });
});

it('handles audio styles correctly with ffmpeg queue [standalone]', function () {
    # prepare
    $this->attachment->file = mp3();
    $this->attachment->type = LaruploadFileType::AUDIO;
    $this->attachment->audio('low', new Wav);
    $this->attachment->ffmpegQueue = true;
    $this->attachment->mode = LaruploadMode::STANDALONE;

    $this->attachment->output = Output::make(
        name: 'test-audio.mp3',
    );


    # before
    $files = Storage::disk($this->attachment->disk)->allFiles();
    expect($files)->toBeEmpty();


    # action
    HandleStylesAction::make($this->attachment)->run(651, Larupload::class, true);


    # test
    $files = Storage::disk($this->attachment->disk)->allFiles();
    expect($files)->toHaveCount(0);

    Queue::assertPushed(ProcessFFMpeg::class, 1);
    Queue::assertPushed(function (ProcessFFMpeg $job) {
        return $job->getId() === 651;
    });
});


it('throws exception when ffmpeg queue exceeds max number', function () {
    # prepare
    $this->attachment->file = mp3();
    $this->attachment->type = LaruploadFileType::AUDIO;
    $this->attachment->audio('low', new Wav);
    $this->attachment->ffmpegQueue = true;
    $this->attachment->ffmpegMaxQueueNum = 1;

    $this->attachment->output = Output::make(
        name: 'test-audio.mp3',
    );


    # works
    HandleStylesAction::make($this->attachment)->run($this->attachment->id, $this->model);

    $files = Storage::disk($this->attachment->disk)->allFiles();
    expect($files)->toHaveCount(0);

    Queue::assertPushed(ProcessFFMpeg::class, 1);
    Queue::assertPushed(function (ProcessFFMpeg $job) {
        return $job->getId() === 52;
    });


    # does not work
    try {
        HandleStylesAction::make($this->attachment)->run($this->attachment->id, $this->model);

        expect(true)->toBeFalse();
    }
    catch (Exception $e) {
        expect($e)
            ->toBeInstanceOf(FFMpegQueueMaxNumExceededException::class)
            ->and($e->getMessage())
            ->toBe(
                trans('larupload::messages.max-queue-num-exceeded')
            );
    }
});
