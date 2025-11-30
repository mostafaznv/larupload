<?php

use Illuminate\Support\Facades\Storage;
use Mostafaznv\Larupload\Actions\Attachment\SaveAttachmentAction;
use Mostafaznv\Larupload\Enums\LaruploadFileType;
use Mostafaznv\Larupload\Enums\LaruploadMediaStyle;
use Mostafaznv\Larupload\Larupload;
use Mostafaznv\Larupload\Storage\Attachment;
use Mostafaznv\Larupload\Test\Support\Enums\LaruploadTestModels;
use Mostafaznv\Larupload\Test\Support\LaruploadTestConsts;


beforeEach(function () {
    $this->disk = 'public';

    Storage::fake($this->disk);

    $this->attachment = Attachment::make('main_file')->disk($this->disk);
    $this->attachment->folder = 'test-folder';
    $this->attachment->nameKebab = 'main-file';

    $this->attachment->file = jpg();
    $this->attachment->cover = null;
    $this->attachment->type = LaruploadFileType::IMAGE;


    $this->action = SaveAttachmentAction::make($this->attachment);

    $this->model = LaruploadTestModels::HEAVY->instance();
    $this->model->id = 1;
});


it('wont crash if file and cover are unset', function () {
    unset($this->attachment->file);
    unset($this->attachment->cover);

    $model = $this->action->execute($this->model);
    $attributes = $model->getAttributes();


    expect($attributes)
        ->toBeArray()
        ->toHaveCount(1)
        ->toHaveKey('id');
});

it('will clean the entire disk directory if file is false', function () {
    # prepare
    $this->attachment->file = false;

    $path = larupload_relative_path($this->attachment, $this->model->id);
    Storage::disk($this->attachment->disk)->put("$path/file.txt", 'test-content');


    # before
    $files = Storage::disk($this->attachment->disk)->allFiles();
    expect($files)->toHaveCount(1);


    # action
    $this->action->execute($this->model);


    # test
    $files = Storage::disk($this->attachment->disk)->allFiles();
    expect($files)->toBeEmpty();
});

it('will upload the original file, cover, and styles', function () {
    # prepare
    $this->attachment->image('small', 200, 200, LaruploadMediaStyle::CROP);

    # action
    $model = SaveAttachmentAction::make($this->attachment)->execute($this->model);


    # test
    $attributes = $model->getAttributes();
    $files = Storage::disk($this->attachment->disk)->allFiles();

    $path = larupload_relative_path($this->attachment, $this->attachment->id);
    $original = $path . '/' . Larupload::ORIGINAL_FOLDER;
    $cover = $path . '/' . Larupload::COVER_FOLDER;
    $small = $path . '/small';

    $hash = LaruploadTestConsts::IMAGE_DETAILS['jpg']['name']['hash'];

    expect($attributes)
        ->toBeArray()
        ->toHaveCount(13)
        ->toHaveKey('id', $this->model->id)
        ->toHaveKey('main_file_file_id', $this->model->id)
        ->toHaveKey('main_file_file_name', $hash)
        ->toHaveKey('main_file_file_original_name', 'image.jpg')
        ->toHaveKey('main_file_file_size', LaruploadTestConsts::IMAGE_DETAILS['jpg']['size'])
        ->toHaveKey('main_file_file_type', LaruploadFileType::IMAGE->name)
        ->toHaveKey('main_file_file_mime_type', LaruploadTestConsts::IMAGE_DETAILS['jpg']['mime_type'])
        ->toHaveKey('main_file_file_width', LaruploadTestConsts::IMAGE_DETAILS['jpg']['width'])
        ->toHaveKey('main_file_file_height', LaruploadTestConsts::IMAGE_DETAILS['jpg']['height'])
        ->toHaveKey('main_file_file_duration', null)
        ->toHaveKey('main_file_file_dominant_color', LaruploadTestConsts::IMAGE_DETAILS['jpg']['color'])
        ->toHaveKey('main_file_file_format', 'jpg')
        ->toHaveKey('main_file_file_cover', $hash)
        # files
        ->and($files)
        ->toBeArray()
        ->toHaveCount(3)
        ->toContain("$original/$hash")
        ->toContain("$cover/$hash")
        ->toContain("$small/$hash");
});

it('will clean old files in the directory before uploading anything new', function () {
    # prepare
    $path = larupload_relative_path($this->attachment, $this->model->id);
    $original = $path . '/' . Larupload::ORIGINAL_FOLDER;
    $cover = $path . '/' . Larupload::COVER_FOLDER;
    $hash = LaruploadTestConsts::IMAGE_DETAILS['jpg']['name']['hash'];

    Storage::disk($this->attachment->disk)->put("$path/file.txt", 'test-content');


    # before
    $files = Storage::disk($this->attachment->disk)->allFiles();
    expect($files)
        ->toHaveCount(1)
        ->toContain("$path/file.txt");


    # action
    $this->action->execute($this->model);


    # test
    $files = Storage::disk($this->attachment->disk)->allFiles();

    expect($files)
        ->toHaveCount(2)
        ->not->toContain("$path/file.txt")
        ->toContain("$original/$hash")
        ->toContain("$cover/$hash");
});

it('wont clean old files in the directory if the corresponding flag is true', function () {
    # prepare
    $this->attachment->keepOldFiles(true);
    $path = larupload_relative_path($this->attachment, $this->model->id);
    $original = $path . '/' . Larupload::ORIGINAL_FOLDER;
    $cover = $path . '/' . Larupload::COVER_FOLDER;
    $hash = LaruploadTestConsts::IMAGE_DETAILS['jpg']['name']['hash'];

    Storage::disk($this->attachment->disk)->put("$path/file.txt", 'test-content');


    # before
    $files = Storage::disk($this->attachment->disk)->allFiles();
    expect($files)
        ->toHaveCount(1)
        ->toContain("$path/file.txt");


    # action
    SaveAttachmentAction::make($this->attachment)->execute($this->model);


    # test
    $files = Storage::disk($this->attachment->disk)->allFiles();

    expect($files)
        ->toHaveCount(3)
        ->toContain("$path/file.txt")
        ->toContain("$original/$hash")
        ->toContain("$cover/$hash");
});

it('can upload the cover without the original file', function () {
    # prepare
    unset($this->attachment->file);
    $this->attachment->cover = png();


    # action
    $model = $this->action->execute($this->model);


    # test
    $attributes = $model->getAttributes();
    $files = Storage::disk($this->attachment->disk)->allFiles();

    $path = larupload_relative_path($this->attachment, $this->attachment->id);
    $cover = $path . '/' . Larupload::COVER_FOLDER;

    $hash = LaruploadTestConsts::IMAGE_DETAILS['png']['name']['hash'];


    expect($attributes)
        ->toBeArray()
        ->toHaveCount(13)
        ->toHaveKey('id', $this->model->id)
        ->toHaveKey('main_file_file_id', null)
        ->toHaveKey('main_file_file_name', null)
        ->toHaveKey('main_file_file_original_name', null)
        ->toHaveKey('main_file_file_size', null)
        ->toHaveKey('main_file_file_type', null)
        ->toHaveKey('main_file_file_mime_type', null)
        ->toHaveKey('main_file_file_width', null)
        ->toHaveKey('main_file_file_height', null)
        ->toHaveKey('main_file_file_duration', null)
        ->toHaveKey('main_file_file_dominant_color', null)
        ->toHaveKey('main_file_file_format', null)
        ->toHaveKey('main_file_file_cover', $hash)
        ->and($files)
        ->toBeArray()
        ->toHaveCount(1)
        ->toContain("$cover/$hash");
});
