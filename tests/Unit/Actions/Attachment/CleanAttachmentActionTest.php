<?php

use Illuminate\Support\Facades\Storage;
use Mostafaznv\Larupload\Actions\Attachment\CleanAttachmentAction;
use Mostafaznv\Larupload\Storage\Attachment;
use Mostafaznv\Larupload\DTOs\Style\Output;


beforeEach(function () {
    $this->disk = 'public';

    Storage::fake($this->disk);
    $this->storage = Storage::disk($this->disk);

    $this->attachment = Attachment::make('test_name')->disk($this->disk);
    $this->attachment->id = 'test-id';
    $this->attachment->folder = 'test-folder';
    $this->attachment->nameKebab = 'test-name';
    $this->attachment->output = Output::make(name: '284191.txt', originalName: 'file.txt');


    $this->path = larupload_relative_path($this->attachment, $this->attachment->id);

    $this->storage->makeDirectory($this->path);
    $this->storage->put("$this->path/test.txt", 'Sample content');


    $this->action = resolve(CleanAttachmentAction::class);
});


it('cleans attachment directory', function () {
    # before
    expect($this->storage->exists($this->path))
        ->toBeTrue()
        ->and($this->storage->allFiles($this->path))
        ->toHaveCount(1)
        ->and($this->attachment->output->name)
        ->toBe('284191.txt')
        ->and($this->attachment->output->originalName)
        ->toBe('file.txt');


    # action
    $result = ($this->action)($this->attachment);


    # test
    expect($result)
        ->toBeInstanceOf(Attachment::class)
        # general
        ->and($result->name)
        ->toBe($this->attachment->name)
        ->and($result->id)
        ->toBe($this->attachment->id)
        ->and($result->folder)
        ->toBe($this->attachment->folder)
        ->and($result->nameKebab)
        ->toBe($this->attachment->nameKebab)
        # output
        ->and($this->attachment->output->name)
        ->toBeNull()
        ->and($this->attachment->output->originalName)
        ->toBeNull()
        # files
        ->and($this->storage->exists($this->path))
        ->toBeFalse()
        ->and($this->storage->allFiles($this->path))
        ->toHaveCount(0);
});


it('does not throw error when directory does not exist', function () {
    # before
    expect($this->storage->exists($this->path))
        ->toBeTrue()
        ->and($this->storage->allFiles($this->path))
        ->toHaveCount(1)
        ->and($this->attachment->output->name)
        ->toBe('284191.txt')
        ->and($this->attachment->output->originalName)
        ->toBe('file.txt');

    $this->storage->deleteDirectory($this->path);

    expect($this->storage->exists($this->path))
        ->toBeFalse()
        ->and($this->storage->allFiles($this->path))
        ->toHaveCount(0);


    # action
    $result = ($this->action)($this->attachment);


    # test
    expect($result)
        ->toBeInstanceOf(Attachment::class)
        # general
        ->and($result->name)
        ->toBe($this->attachment->name)
        ->and($result->id)
        ->toBe($this->attachment->id)
        ->and($result->folder)
        ->toBe($this->attachment->folder)
        ->and($result->nameKebab)
        ->toBe($this->attachment->nameKebab)
        # output
        ->and($this->attachment->output->name)
        ->toBeNull()
        ->and($this->attachment->output->originalName)
        ->toBeNull()
        # files
        ->and($this->storage->exists($this->path))
        ->toBeFalse()
        ->and($this->storage->allFiles($this->path))
        ->toHaveCount(0);
});
