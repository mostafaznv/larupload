<?php

use FFMpeg\Format\Audio\Wav;
use Mostafaznv\Larupload\Actions\HandleModelStylesAction;
use Mostafaznv\Larupload\DTOs\Style\Output;
use Mostafaznv\Larupload\Enums\LaruploadFileType;
use Mostafaznv\Larupload\Larupload;
use Mostafaznv\Larupload\Storage\Attachment;
use Mostafaznv\Larupload\Test\Support\Enums\LaruploadTestModels;


beforeEach(function () {
    $this->disk = 'public';

    Queue::fake();
    Storage::fake($this->disk);

    $this->attachment = Attachment::make('main_file');
    $this->attachment->disk = $this->disk;
    $this->attachment->localDisk = $this->disk;
    $this->attachment->folder = 'test-folder';
    $this->attachment->nameKebab = 'main-file';
    $this->attachment->id = 'test-id';
    $this->attachment->file = mp3();
    $this->attachment->type = LaruploadFileType::AUDIO;
    $this->attachment->audio('wav', new Wav);

    $this->attachment->output = Output::make(
        name: 'test-audio.mp3',
    );

    $this->path = larupload_relative_path($this->attachment, $this->attachment->id);
    $this->original = $this->path . '/' . Larupload::ORIGINAL_FOLDER;
    $this->cover = $this->path . '/' . Larupload::COVER_FOLDER;

    $this->model = LaruploadTestModels::HEAVY->instance();
    $this->model->id = 52;


    $this->action = resolve(HandleModelStylesAction::class);
});


it('processes styles for attachments that require processing', function () {
    # prepare
    $this->attachment->shouldProcessStyles = true;

    # before
    $files = Storage::disk($this->attachment->disk)->allFiles();
    expect($files)->toBeEmpty();


    # action
    ($this->action)($this->model, [$this->attachment]);


    # test
    $files = Storage::disk($this->attachment->disk)->allFiles();
    expect($files)
        ->toHaveCount(1)
        ->toBe([
            "$this->path/wav/test-audio.wav"
        ]);
});

it('does not process styles for attachments that do not require processing', function () {
    # prepare
    $this->attachment->shouldProcessStyles = false;

    # before
    $files = Storage::disk($this->attachment->disk)->allFiles();
    expect($files)->toBeEmpty();


    # action
    ($this->action)($this->model, [$this->attachment]);


    # test
    $files = Storage::disk($this->attachment->disk)->allFiles();
    expect($files)->toBeEmpty();
});
