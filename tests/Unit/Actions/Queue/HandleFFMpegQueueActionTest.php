<?php

use FFMpeg\Format\Audio\Wav;
use FFMpeg\Format\Video\X264;
use Mostafaznv\Larupload\Actions\Queue\HandleFFMpegQueueAction;
use Mostafaznv\Larupload\DTOs\Style\Output;
use Mostafaznv\Larupload\Enums\LaruploadMode;
use Mostafaznv\Larupload\Enums\LaruploadSecureIdsMethod;
use Mostafaznv\Larupload\Larupload;
use Mostafaznv\Larupload\Storage\Attachment;


beforeEach(function () {
    $this->disk = 'public';

    Storage::fake('s3');
    Storage::fake($this->disk);
    $this->storage = Storage::disk($this->disk);

    $this->attachment = Attachment::make('test_name');
    $this->attachment->id = 'test-id';
    $this->attachment->folder = 'test-folder';
    $this->attachment->nameKebab = 'test-name';
    $this->attachment->disk = $this->disk;
    $this->attachment->localDisk = $this->disk;

    $this->original = larupload_relative_path($this->attachment, $this->attachment->id, Larupload::ORIGINAL_FOLDER);
    $this->storage->makeDirectory($this->original);

    $this->setOutput = function (string $name) {
        $this->attachment->output = Output::make(
            name: $name,
        );
    };


    $this->action = resolve(HandleFFMpegQueueAction::class);
});


it('handles video file type and processes video styles', function () {
    # prepare
    $name = 'video.mp4';
    ($this->setOutput)($name);
    $this->storage->putFileAs($this->original, mp4(), $name);

    $this->attachment->video('small', 400);
    $this->attachment->stream(
        name: '480p',
        width: 640,
        height: 480,
        format: new X264
    );


    # action
    $this->action->execute($this->attachment);


    # test
    $files = $this->storage->allFiles();
    $path = larupload_relative_path($this->attachment, $this->attachment->id);
    $small = "$path/small";
    $stream = $path . '/' . Larupload::STREAM_FOLDER;

    expect($files)
        ->toHaveCount(6)
        ->toContain("$this->original/$name")
        ->toContain("$small/$name")
        ->toContain("$stream/video.m3u8")
        ->toContain("$stream/480p/480p-0.ts")
        ->toContain("$stream/480p/480p-list.m3u8")
        ->toContain("$stream/480p/master.m3u8");
});

it('handles audio file type and processes video styles', function () {
    # prepare
    ($this->setOutput)('audio.mp3');
    $this->storage->putFileAs($this->original, mp3(), 'audio.mp3');

    $this->attachment->audio('audio_wav', new Wav);


    # action
    $this->action->execute($this->attachment);


    # test
    $files = $this->storage->allFiles();
    $path = larupload_relative_path($this->attachment, $this->attachment->id);
    $wav = "$path/audio-wav";


    expect($files)
        ->toHaveCount(2)
        ->toContain("$this->original/audio.mp3")
        ->toContain("$wav/audio.wav");
});

it('deletes local directory when disk is not local [unified attachment ids]', function (LaruploadSecureIdsMethod $method, bool $last, bool $result) {
    # prepare
    $this->attachment->disk = 's3';
    $this->attachment->secureIdsMethod = $method;
    $path = larupload_relative_path($this->attachment, $this->attachment->id);
    $storage = Storage::disk('s3');

    ($this->setOutput)('audio.mp3');
    $this->storage->putFileAs($this->original, mp3(), 'audio.mp3');


    # first file
    $this->attachment->audio('audio_wav1', new Wav);

    $this->action->execute($this->attachment);
    $localFiles = $this->storage->allFiles();
    $remoteFiles = $storage->allFiles();


    expect($localFiles)
        ->toHaveCount(1)
        ->toContain("$this->original/audio.mp3")
        ->and($remoteFiles)
        ->toHaveCount(1)
        ->toContain("$path/audio-wav1/audio.wav");


    # second file
    $this->attachment->audioStyles = [];
    $this->attachment->audio('audio_wav2', new Wav);

    $this->action->execute($this->attachment);
    $localFiles = $this->storage->allFiles();
    $remoteFiles = $storage->allFiles();

    expect($localFiles)
        ->toHaveCount(1)
        ->toContain("$this->original/audio.mp3")
        ->and($remoteFiles)
        ->toHaveCount(2)
        ->toContain("$path/audio-wav1/audio.wav")
        ->toContain("$path/audio-wav2/audio.wav");


    # last file
    $this->attachment->audioStyles = [];
    $this->attachment->audio('audio_wav3', new Wav);

    $this->action->execute($this->attachment, $last);
    $localFiles = $this->storage->allFiles();
    $remoteFiles = $storage->allFiles();

    if ($result) {
        expect($localFiles)
            ->toHaveCount(0)
            ->and($remoteFiles)
            ->toHaveCount(3)
            ->toContain("$path/audio-wav1/audio.wav")
            ->toContain("$path/audio-wav2/audio.wav")
            ->toContain("$path/audio-wav3/audio.wav");
    }
    else {
        expect($localFiles)
            ->toHaveCount(1)
            ->toContain("$this->original/audio.mp3")
            ->and($remoteFiles)
            ->toHaveCount(3)
            ->toContain("$path/audio-wav1/audio.wav")
            ->toContain("$path/audio-wav2/audio.wav")
            ->toContain("$path/audio-wav3/audio.wav");
    }

})->with([
    'hashid-last'  => fn() => [
        'method' => LaruploadSecureIdsMethod::HASHID,
        'last'   => true,
        'result' => true,
    ],
    'hashid-first' => fn() => [
        'method' => LaruploadSecureIdsMethod::HASHID,
        'last'   => false,
        'result' => false,
    ],
    'sqid-last'    => fn() => [
        'method' => LaruploadSecureIdsMethod::SQID,
        'last'   => true,
        'result' => true,
    ],
    'sqid-first'   => fn() => [
        'method' => LaruploadSecureIdsMethod::SQID,
        'last'   => false,
        'result' => false,
    ],
    'none-last'    => fn() => [
        'method' => LaruploadSecureIdsMethod::NONE,
        'last'   => true,
        'result' => true,
    ],
    'none-first'   => fn() => [
        'method' => LaruploadSecureIdsMethod::NONE,
        'last'   => false,
        'result' => false,
    ],
]);

it('deletes local directory when disk is not local [not-unified attachment ids]', function (LaruploadSecureIdsMethod $method, bool $last) {
    # prepare
    $localStorage = Storage::disk($this->disk);
    $remoteStorage = Storage::disk('s3');

    $attachment1 = Attachment::make('test_name1');
    $attachment1->id = 'test-id';
    $attachment1->folder = 'test-folder1';
    $attachment1->nameKebab = 'test-name1';
    $attachment1->disk = 's3';
    $attachment1->localDisk = $this->disk;
    $attachment1->secureIdsMethod = $method;
    $attachment1->output = Output::make(name: 'audio.mp3');
    $attachment1->audio('audio_wav', new Wav);

    $attachment2 = Attachment::make('test_name2');
    $attachment2->id = 'test-id';
    $attachment2->folder = 'test-folder2';
    $attachment2->nameKebab = 'test-name2';
    $attachment2->disk = 's3';
    $attachment2->localDisk = $this->disk;
    $attachment2->secureIdsMethod = $method;
    $attachment2->output = Output::make(name: 'audio.mp3');
    $attachment2->audio('audio_wav', new Wav);

    $path1 = larupload_relative_path($attachment1, $attachment1->id);
    $path2 = larupload_relative_path($attachment2, $attachment2->id);
    $original1 = $path1 . '/' . Larupload::ORIGINAL_FOLDER;
    $original2 = $path2 . '/' . Larupload::ORIGINAL_FOLDER;

    $localStorage->makeDirectory($original1);
    $localStorage->makeDirectory($original2);

    $localStorage->putFileAs($original1, mp3(), 'audio.mp3');
    $localStorage->putFileAs($original2, mp3(), 'audio.mp3');


    # before
    $localFiles = $localStorage->allFiles();
    $remoteFiles = $remoteStorage->allFiles();

    expect($localFiles)
        ->toHaveCount(2)
        ->toContain("$original1/audio.mp3")
        ->toContain("$original2/audio.mp3")
        ->and($remoteFiles)
        ->toHaveCount(0);


    # action
    $this->action->execute($attachment2, $last);


    # test
    $localFiles = $localStorage->allFiles();
    $remoteFiles = $remoteStorage->allFiles();

    expect($localFiles)
        ->toHaveCount(1)
        ->toContain("$original1/audio.mp3")
        ->and($remoteFiles)
        ->toHaveCount(1)
        ->toContain("$path2/audio-wav/audio.wav");


})->with([
    'uuid-last'  => fn() => [
        'method' => LaruploadSecureIdsMethod::UUID,
        'last'   => true,
    ],
    'uuid-first' => fn() => [
        'method' => LaruploadSecureIdsMethod::UUID,
        'last'   => false,
    ],
    'ulid-last'  => fn() => [
        'method' => LaruploadSecureIdsMethod::ULID,
        'last'   => true,
        'result' => true,
    ],
    'ulid-first' => fn() => [
        'method' => LaruploadSecureIdsMethod::ULID,
        'last'   => false,
    ],
]);

it('deletes standalone directory when disk is not local', function () {
    # prepare
    $this->attachment->mode = LaruploadMode::STANDALONE;
    $path = larupload_relative_path($this->attachment, $this->attachment->id);
    $original = larupload_relative_path($this->attachment, $this->attachment->id, Larupload::ORIGINAL_FOLDER);
    $this->attachment->disk = 's3';
    $storage = Storage::disk('s3');

    ($this->setOutput)('audio.mp3');
    $this->storage->putFileAs($original, mp3(), 'audio.mp3');
    $this->attachment->audio('audio_wav', new Wav);


    # test
    $this->action->execute($this->attachment, true, true);
    $localFiles = $this->storage->allFiles();
    $remoteFiles = $storage->allFiles();


    expect($localFiles)
        ->toHaveCount(0)
        ->and($remoteFiles)
        ->toHaveCount(1)
        ->toContain("$path/audio-wav/audio.wav");
});
