<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Mostafaznv\Larupload\Actions\Attachment\CleanAttachmentAction;
use Mostafaznv\Larupload\Actions\SetFileNameAction;
use Mostafaznv\Larupload\Enums\LaruploadNamingMethod;
use Mostafaznv\Larupload\Storage\Attachment;
use Mostafaznv\Larupload\DTOs\Style\Output;
use Mostafaznv\Larupload\Test\Support\LaruploadTestConsts;
use function Spatie\PestPluginTestTime\testTime;


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

it('generates file name using hash method', function () {
    $result = SetFileNameAction::make(jpg(), LaruploadNamingMethod::HASH_FILE)->generate();

    expect($result)->toBe(LaruploadTestConsts::IMAGE_DETAILS['jpg-fa']['name']['hash']);
});

it('generates file name using time method', function () {
    testTime()->freeze('2024-01-01 12:00:00');

    $result = SetFileNameAction::make(jpg(), LaruploadNamingMethod::TIME)->generate();

    expect($result)->toBe('1704110400.jpg');
});

it('generates slugged file name with random number', function () {
    $result = SetFileNameAction::make(jpg(true), LaruploadNamingMethod::SLUG, 'fa')->generate();

    expect($result)->toStartWith(LaruploadTestConsts::IMAGE_DETAILS['jpg-fa']['name']['slug']);
});

