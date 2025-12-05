<?php

use Mostafaznv\Larupload\Actions\RetrieveStandaloneMetaFileAction;
use Mostafaznv\Larupload\DTOs\Style\Output;
use Mostafaznv\Larupload\Enums\LaruploadFileType;
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

    $this->meta = larupload_relative_path($this->attachment, $this->attachment->id) . '/.meta';

    $this->action = resolve(RetrieveStandaloneMetaFileAction::class);
});

it('returns null when meta file does not exist', function () {
    # prepare
    Storage::shouldReceive('disk->exists')->with($this->meta)->andReturn(false);

    # action
    $result = ($this->action)($this->attachment);


    # test
    expect($result)->toBeNull();
});

it('returns null when meta file does not contain meta property', function () {
    # prepare
    Storage::shouldReceive('disk->exists')->with($this->meta)->andReturn(true);
    Storage::shouldReceive('disk->get')->with($this->meta)->andReturn(json_encode([
        'id' => 'test-1'
    ]));


    # action
    $result = ($this->action)($this->attachment);


    # test
    expect($result)->toBeNull();
});

it('returns output when meta file exists', function () {
    # prepare
    Storage::shouldReceive('disk->exists')->with($this->meta)->andReturn(true);
    Storage::shouldReceive('disk->get')->with($this->meta)->andReturn(json_encode([
        'meta' => [
            'id'             => 'test-id',
            'name'           => '248910.jpg',
            'original_name'  => 'image.png',
            'mime_type'      => 'image/png',
            'dominant_color' => '#000000',
            'type'           => 'IMAGE',
            'size'           => 220131,
            'width'          => 800,
            'height'         => 600,
            'duration'       => null,
            'format'         => 'png',
            'cover'          => 'cover.png',
        ]
    ]));


    # action
    $result = ($this->action)($this->attachment);


    # test
    expect($result)
        ->toBeInstanceOf(Output::class)
        ->and($result->id)
        ->toBe('test-id')
        ->and($result->name)
        ->toBe('248910.jpg')
        ->and($result->originalName)
        ->toBe('image.png')
        ->and($result->mimeType)
        ->toBe('image/png')
        ->and($result->dominantColor)
        ->toBe('#000000')
        ->and($result->type)
        ->toBe(LaruploadFileType::IMAGE)
        ->and($result->size)
        ->toBe(220131)
        ->and($result->width)
        ->toBe(800)
        ->and($result->height)
        ->toBe(600)
        ->and($result->duration)
        ->toBeNull()
        ->and($result->format)
        ->toBe('png')
        ->and($result->cover)
        ->toBe('cover.png');
});
