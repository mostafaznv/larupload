<?php

use Mostafaznv\Larupload\Actions\RetrieveStandaloneMetaFileAction;
use Mostafaznv\Larupload\Actions\UpdateStandaloneMetaFileAction;
use Mostafaznv\Larupload\DTOs\Style\Output;
use Mostafaznv\Larupload\Enums\LaruploadFileType;
use Mostafaznv\Larupload\Enums\LaruploadMode;
use Mostafaznv\Larupload\Exceptions\InvalidImageOptimizerException;
use Illuminate\Http\UploadedFile;
use Mostafaznv\Larupload\Actions\OptimizeImageAction;
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

    $this->action = resolve(UpdateStandaloneMetaFileAction::class);
});


it('updates meta file with provided urls', function () {
    # prepare
    $urls = (object)['key1' => 'value1', 'key2' => 'value2'];
    $meta = larupload_relative_path($this->attachment, $this->attachment->id) . '/.meta';


    # test
    $res = Storage::disk($this->attachment->disk)->get($meta);
    expect($res)->toBeNull();


    # action
    ($this->action)($this->attachment, $urls);


    # test
    $res = Storage::disk($this->attachment->disk)->get($meta);

    expect($res)
        ->toBeJson()
        ->and(json_decode($res, true))
        ->toHaveCount(2)
        ->toHaveKey('key1', 'value1')
        ->toHaveKey('key2', 'value2');
});

it('updates meta file with generated urls when none provided', function () {
    # prepare
    $attachment = Mockery::mock(Attachment::class);
    $attachment->id = '123';
    $attachment->disk = $this->disk;
    $attachment->mode = LaruploadMode::HEAVY;
    $attachment->nameKebab = 'main-file';
    $urls = (object)['key1' => 'value1', 'key2' => 'value2'];

    $attachment->shouldReceive('urls')->andReturn($urls);

    $meta = larupload_relative_path($attachment, $attachment->id) . '/.meta';


    # test
    $res = Storage::disk($attachment->disk)->get($meta);
    expect($res)->toBeNull();


    # action
    ($this->action)($attachment, null);


    # test
    $res = Storage::disk($attachment->disk)->get($meta);

    expect($res)
        ->toBeJson()
        ->and(json_decode($res, true))
        ->toHaveCount(2)
        ->toHaveKey('key1', 'value1')
        ->toHaveKey('key2', 'value2');
});
