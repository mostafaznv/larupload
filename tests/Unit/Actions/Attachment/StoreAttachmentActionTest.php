<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Mostafaznv\Larupload\Actions\Attachment\StoreAttachmentAction;
use Mostafaznv\Larupload\Enums\LaruploadFileType;
use Mostafaznv\Larupload\Enums\LaruploadMode;
use Mostafaznv\Larupload\Larupload;
use Mostafaznv\Larupload\Storage\Attachment;
use Mostafaznv\Larupload\Test\Support\Enums\LaruploadTestModels;
use Mostafaznv\Larupload\Test\Support\LaruploadTestConsts;


beforeEach(function () {
    $this->disk = 'public';

    Storage::fake($this->disk);

    $this->attachment = Attachment::make('test_name')->disk($this->disk);
    $this->attachment->id = 'test-id';
    $this->attachment->folder = 'test-folder';
    $this->attachment->nameKebab = 'test-name';

    $this->attachment->file = jpg();
    $this->attachment->type = LaruploadFileType::IMAGE;
});


# basic
it('saves basic attachment attributes correctly', function () {
    $action = new class($this->attachment) extends StoreAttachmentAction {
        public function run(): void
        {
            $this->basic();
        }

        public function attachment(): Attachment
        {
            return $this->attachment;
        }
    };

    $action->run();
    $attachment = $action->attachment();
    $output = $attachment->output;

    expect($output->name)
        ->toBe(LaruploadTestConsts::IMAGE_DETAILS['jpg']['name']['hash'])
        ->and($output->originalName)
        ->toBe('image.jpg')
        ->and($output->id)
        ->toBe('test-id')
        ->and($output->format)
        ->toBe('jpg')
        ->and($output->size)
        ->toBe(LaruploadTestConsts::IMAGE_DETAILS['jpg']['size'])
        ->and($output->type)
        ->toBe(LaruploadFileType::IMAGE)
        ->and($output->mimeType)
        ->toBe(LaruploadTestConsts::IMAGE_DETAILS['jpg']['mime_type']);
});


# media
it('extracts image metadata correctly', function () {
    $action = new class($this->attachment) extends StoreAttachmentAction {
        public function run(): void
        {
            $this->media();
        }

        public function attachment(): Attachment
        {
            return $this->attachment;
        }
    };

    $action->run();
    $attachment = $action->attachment();
    $output = $attachment->output;

    expect($output->width)
        ->toBe(LaruploadTestConsts::IMAGE_DETAILS['jpg']['width'])
        ->and($output->height)
        ->toBe(LaruploadTestConsts::IMAGE_DETAILS['jpg']['height'])
        ->and($output->dominantColor)
        ->toBe(LaruploadTestConsts::IMAGE_DETAILS['jpg']['color']);
});

it('extracts video metadata correctly', function () {
    $this->attachment->file = mp4();
    $this->attachment->type = LaruploadFileType::VIDEO;

    $action = new class($this->attachment) extends StoreAttachmentAction {
        public function run(): void
        {
            $this->media();
        }

        public function attachment(): Attachment
        {
            return $this->attachment;
        }
    };

    $action->run();
    $attachment = $action->attachment();
    $output = $attachment->output;

    expect($output->width)
        ->toBe(LaruploadTestConsts::VIDEO_DETAILS['width'])
        ->and($output->height)
        ->toBe(LaruploadTestConsts::VIDEO_DETAILS['height'])
        ->and($output->duration)
        ->toBe(LaruploadTestConsts::VIDEO_DETAILS['duration']);
});

it('extracts audio metadata correctly', function () {
    $this->attachment->file = mp3();
    $this->attachment->type = LaruploadFileType::AUDIO;

    $action = new class($this->attachment) extends StoreAttachmentAction {
        public function run(): void
        {
            $this->media();
        }

        public function attachment(): Attachment
        {
            return $this->attachment;
        }
    };

    $action->run();
    $attachment = $action->attachment();
    $output = $attachment->output;

    expect($output->width)
        ->toBeNull()
        ->and($output->height)
        ->toBeNull()
        ->and($output->duration)
        ->toBe(LaruploadTestConsts::AUDIO_DETAILS['duration']);
});


# set-attributes
it('stores attributes', function () {
    $this->attachment->cover = jpg();

    $action = new class($this->attachment) extends StoreAttachmentAction {
        public function run(): self
        {
            $this->basic();
            $this->media();
            $this->setCover($this->attachment->id);

            return $this;
        }

        public function set(Model $model, LaruploadMode $mode): Model
        {
            $this->attachment->mode = $mode;

            return $this->setAttributes($model);
        }
    };

    $action->run();

    # heavy
    $model = LaruploadTestModels::HEAVY->instance();
    $model = $action->set($model, LaruploadMode::HEAVY);

    expect($model->getAttribute('test_name_file_id'))
        ->toBe('test-id')
        ->and($model->getAttribute('test_name_file_name'))
        ->toBe(LaruploadTestConsts::IMAGE_DETAILS['jpg']['name']['hash'])
        ->and($model->getAttribute('test_name_file_original_name'))
        ->toBe('image.jpg')
        ->and($model->getAttribute('test_name_file_size'))
        ->toBe(LaruploadTestConsts::IMAGE_DETAILS['jpg']['size'])
        ->and($model->getAttribute('test_name_file_type'))
        ->toBe(LaruploadFileType::IMAGE->name)
        ->and($model->getAttribute('test_name_file_mime_type'))
        ->toBe(LaruploadTestConsts::IMAGE_DETAILS['jpg']['mime_type'])
        ->and($model->getAttribute('test_name_file_width'))
        ->toBe(LaruploadTestConsts::IMAGE_DETAILS['jpg']['width'])
        ->and($model->getAttribute('test_name_file_height'))
        ->toBe(LaruploadTestConsts::IMAGE_DETAILS['jpg']['height'])
        ->and($model->getAttribute('test_name_file_duration'))
        ->toBeNull()
        ->and($model->getAttribute('test_name_file_dominant_color'))
        ->toBe(LaruploadTestConsts::IMAGE_DETAILS['jpg']['color'])
        ->and($model->getAttribute('test_name_file_format'))
        ->toBe('jpg')
        ->and($model->getAttribute('test_name_file_cover'))
        ->toBe(LaruploadTestConsts::IMAGE_DETAILS['jpg']['name']['hash']);


    # light
    $model = LaruploadTestModels::LIGHT->instance();
    $model = $action->set($model, LaruploadMode::LIGHT);

    expect($model->getAttribute('test_name_file_name'))
        ->toBe(LaruploadTestConsts::IMAGE_DETAILS['jpg']['name']['hash'])
        ->and($model->getAttribute('test_name_file_meta'))
        ->toBeJson()
        ->and(json_decode($model->getAttribute('test_name_file_meta')))
        ->toHaveKey('id', 'test-id')
        ->toHaveKey('name', LaruploadTestConsts::IMAGE_DETAILS['jpg']['name']['hash'])
        ->toHaveKey('original_name', 'image.jpg')
        ->toHaveKey('size', LaruploadTestConsts::IMAGE_DETAILS['jpg']['size'])
        ->toHaveKey('type', LaruploadFileType::IMAGE->name)
        ->toHaveKey('mime_type', LaruploadTestConsts::IMAGE_DETAILS['jpg']['mime_type'])
        ->toHaveKey('width', LaruploadTestConsts::IMAGE_DETAILS['jpg']['width'])
        ->toHaveKey('height', LaruploadTestConsts::IMAGE_DETAILS['jpg']['height'])
        ->toHaveKey('duration', null)
        ->toHaveKey('dominant_color', LaruploadTestConsts::IMAGE_DETAILS['jpg']['color'])
        ->toHaveKey('format', 'jpg')
        ->toHaveKey('cover', LaruploadTestConsts::IMAGE_DETAILS['jpg']['name']['hash']);
});


# upload file
it('uploads the original file', function () {
    # prepare
    Storage::fake();

    $action = new class($this->attachment) extends StoreAttachmentAction {
        public function run(): void
        {
            $this->basic();
            $this->uploadOriginalFile($this->attachment->id);
        }
    };


    # before
    $files = Storage::disk($this->attachment->disk)->allFiles();
    expect($files)->toBeEmpty();


    # action
    $action->run();


    # test
    $path = larupload_relative_path($this->attachment, $this->attachment->id, Larupload::ORIGINAL_FOLDER);
    $files = Storage::disk($this->attachment->disk)->allFiles();

    expect($files)->toBe([
        $path . '/' . LaruploadTestConsts::IMAGE_DETAILS['jpg']['name']['hash'],
    ]);
});

it('uploads the original file using a custom disk', function () {
    # prepare
    Storage::fake($this->attachment->disk);
    Storage::fake('s3');

    $action = new class($this->attachment) extends StoreAttachmentAction {
        public function run(): void
        {
            $this->basic();
            $this->uploadOriginalFile($this->attachment->id, 's3');
        }
    };


    # before
    $default = Storage::disk($this->attachment->disk)->allFiles();
    $s3 = Storage::disk('s3')->allFiles();

    expect($default)
        ->toBeEmpty()
        ->and($s3)
        ->toBeEmpty();


    # action
    $action->run();


    # test
    $path = larupload_relative_path($this->attachment, $this->attachment->id, Larupload::ORIGINAL_FOLDER);
    $default = Storage::disk($this->attachment->disk)->allFiles();
    $s3 = Storage::disk('s3')->allFiles();

    expect($default)
        ->toBeEmpty()
        ->and($s3)
        ->toBe([
            $path . '/' . LaruploadTestConsts::IMAGE_DETAILS['jpg']['name']['hash'],
        ]);
});


# set-cover
it('can set cover', function () {
    # prepare
    Storage::fake();
    $this->attachment->cover = png();

    $action = new class($this->attachment) extends StoreAttachmentAction {
        public function run(): void
        {
            $this->basic();
            $this->uploadOriginalFile($this->attachment->id);
            $this->setCover($this->attachment->id);
        }
    };


    # before
    $files = Storage::disk($this->attachment->disk)->allFiles();
    expect($files)->toBeEmpty();


    # action
    $action->run();


    # test
    $original = larupload_relative_path($this->attachment, $this->attachment->id, Larupload::ORIGINAL_FOLDER);
    $cover = larupload_relative_path($this->attachment, $this->attachment->id, Larupload::COVER_FOLDER);
    $files = Storage::disk($this->attachment->disk)->allFiles();

    expect($files)
        ->toHaveCount(2)
        ->toContain(
            $cover . '/' . LaruploadTestConsts::IMAGE_DETAILS['png']['name']['hash'],
            $original . '/' . LaruploadTestConsts::IMAGE_DETAILS['jpg']['name']['hash'],
        );
});


# clean
it('can clean the entire disk directory', function () {
    # prepare
    Storage::fake();

    $action = new class($this->attachment) extends StoreAttachmentAction {
        public function init(): self
        {
            $this->basic();
            $this->uploadOriginalFile($this->attachment->id);

            return $this;
        }

        public function run(): void
        {
            $this->clean();
        }
    };

    $action->init();


    # before
    $files = Storage::disk($this->attachment->disk)->allFiles();
    expect($files)->toHaveCount(1);


    # action
    $action->run();


    # test
    $files = Storage::disk($this->attachment->disk)->allFiles();
    expect($files)->toBeEmpty();
});
