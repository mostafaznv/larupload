<?php

use Illuminate\Support\Facades\Storage;
use Mostafaznv\Larupload\Actions\Attachment\SaveStandaloneAttachmentAction;
use Mostafaznv\Larupload\Enums\LaruploadFileType;
use Mostafaznv\Larupload\Enums\LaruploadMediaStyle;
use Mostafaznv\Larupload\Larupload;
use Mostafaznv\Larupload\Storage\Attachment;
use Mostafaznv\Larupload\Test\Support\LaruploadTestConsts;


beforeEach(function () {
    $this->disk = 'public';

    Storage::fake($this->disk);

    $this->attachment = Attachment::make('main_file')->disk($this->disk);
    $this->attachment->folder = 'test-folder';
    $this->attachment->nameKebab = 'main-file';
    $this->attachment->id = 'test-id';

    $this->attachment->file = jpg();
    $this->attachment->cover = null;
    $this->attachment->type = LaruploadFileType::IMAGE;

    $this->path = larupload_relative_path($this->attachment, $this->attachment->id);
    $this->original = $this->path . '/' . Larupload::ORIGINAL_FOLDER;
    $this->cover = $this->path . '/' . Larupload::COVER_FOLDER;


    $this->action = SaveStandaloneAttachmentAction::make($this->attachment);
});


it('cleans attachment directory when id is provided', function () {
    # before
    Storage::disk($this->disk)->put("$this->path/file.txt", 'test content');

    $files = Storage::disk($this->attachment->disk)->allFiles();

    expect($files)
        ->toHaveCount(1)
        ->toBe([
            "$this->path/file.txt",
        ]);


    # action
    $this->action->execute();


    # test
    $files = Storage::disk($this->attachment->disk)->allFiles();
    $hash = LaruploadTestConsts::IMAGE_DETAILS['jpg']['name']['hash'];

    expect($files)
        ->toHaveCount(3)
        ->toContain("$this->path/.meta")
        ->toContain("$this->original/$hash")
        ->toContain("$this->cover/$hash");
});

it('will upload the original file, cover, and styles', function () {
    # prepare
    $this->attachment->image('small', 200, 200, LaruploadMediaStyle::CROP);


    # action
    $res = SaveStandaloneAttachmentAction::make($this->attachment)->execute();


    # test
    $disk = Storage::disk($this->attachment->disk);
    $files = $disk->allFiles();

    $hash = LaruploadTestConsts::IMAGE_DETAILS['jpg']['name']['hash'];
    $original = url($disk->url("$this->original/$hash"));
    $cover = url($disk->url("$this->cover/$hash"));
    $small = url($disk->url("$this->path/small/$hash"));

    expect($res)
        ->toBeObject()
        ->toHaveProperty('original', $original)
        ->toHaveProperty('cover', $cover)
        ->toHaveProperty('small', $small)
        ->and($res->meta)
        ->toHaveKey('id', 'test-id')
        ->toHaveKey('name', $hash)
        ->toHaveKey('original_name', 'image.jpg')
        ->toHaveKey('size', LaruploadTestConsts::IMAGE_DETAILS['jpg']['size'])
        ->toHaveKey('type', LaruploadFileType::IMAGE->name)
        ->toHaveKey('mime_type', LaruploadTestConsts::IMAGE_DETAILS['jpg']['mime_type'])
        ->toHaveKey('width', LaruploadTestConsts::IMAGE_DETAILS['jpg']['width'])
        ->toHaveKey('height', LaruploadTestConsts::IMAGE_DETAILS['jpg']['height'])
        ->toHaveKey('duration', null)
        ->toHaveKey('dominant_color', LaruploadTestConsts::IMAGE_DETAILS['jpg']['color'])
        ->toHaveKey('format', 'jpg')
        ->toHaveKey('cover', $hash)
        # files
        ->and($files)
        ->toBeArray()
        ->toHaveCount(4)
        ->toContain("$this->path/.meta")
        ->toContain("$this->original/$hash")
        ->toContain("$this->cover/$hash")
        ->toContain("$this->path/small/$hash");
});

it('will update .meta file with correct data', function () {
    $this->action->execute();

    $disk = Storage::disk($this->attachment->disk);
    $file = $disk->get("$this->path/.meta");
    expect($file)->toBeJson();

    $res = json_decode($file);
    $hash = LaruploadTestConsts::IMAGE_DETAILS['jpg']['name']['hash'];
    $original = url($disk->url("$this->original/$hash"));
    $cover = url($disk->url("$this->cover/$hash"));

    expect($res)
        ->toBeObject()
        ->toHaveProperty('original', $original)
        ->toHaveProperty('cover', $cover)
        ->and($res->meta)
        ->toHaveKey('id', 'test-id')
        ->toHaveKey('name', $hash)
        ->toHaveKey('original_name', 'image.jpg')
        ->toHaveKey('size', LaruploadTestConsts::IMAGE_DETAILS['jpg']['size'])
        ->toHaveKey('type', LaruploadFileType::IMAGE->name)
        ->toHaveKey('mime_type', LaruploadTestConsts::IMAGE_DETAILS['jpg']['mime_type'])
        ->toHaveKey('width', LaruploadTestConsts::IMAGE_DETAILS['jpg']['width'])
        ->toHaveKey('height', LaruploadTestConsts::IMAGE_DETAILS['jpg']['height'])
        ->toHaveKey('duration', null)
        ->toHaveKey('dominant_color', LaruploadTestConsts::IMAGE_DETAILS['jpg']['color'])
        ->toHaveKey('format', 'jpg')
        ->toHaveKey('cover', $hash);
});
