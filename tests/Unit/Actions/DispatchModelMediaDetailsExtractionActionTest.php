<?php

use FFMpeg\Format\Audio\Wav;
use Mostafaznv\Larupload\Actions\DispatchModelMediaDetailsExtractionAction;
use Mostafaznv\Larupload\Actions\Queue\InitializeMediaDetailsQueueAction;
use Mostafaznv\Larupload\DTOs\Style\Output;
use Mostafaznv\Larupload\Enums\LaruploadFileType;
use Mostafaznv\Larupload\Larupload;
use Mostafaznv\Larupload\Storage\Attachment;
use Mostafaznv\Larupload\Test\Support\Enums\LaruploadTestModels;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadHeavyTestModel;


beforeEach(function () {
    $this->disk = 'public';

    config()->set('larupload.extract-media-details-on-queue', true);

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



    app()->instance(
        InitializeMediaDetailsQueueAction::class,
        new class extends InitializeMediaDetailsQueueAction {
            private static array $attachments = [];

            public function __construct()
            {
                self::$attachments = [];
            }

            public function __invoke(Attachment $attachment, int $id, string $class): void
            {
                self::$attachments[] = $attachment->id;
            }

            public function getAttachments(): array
            {
                return self::$attachments;
            }
        }
    );

    $this->action = resolve(DispatchModelMediaDetailsExtractionAction::class);
});


it('extract media details for attachments that require extracting on queue', function () {
    # prepare
    $otherAttachment = clone $this->attachment;
    $otherAttachment->id = 'other-test-id';

    # action
    ($this->action)($this->model, [$this->attachment, $otherAttachment]);


    # test
    $attachments = resolve(InitializeMediaDetailsQueueAction::class)->getAttachments();

    expect($attachments)->toBe([
        'test-id',
        'other-test-id',
    ]);
});

it('does not extract media details when `larupload.extract-media-details-on-queue` is false', function () {
    # prepare
    $this->attachment = $this->attachment->extractMediaDetailsOnQueue(false);


    # action
    ($this->action)($this->model, [$this->attachment]);


    # test
    $attachments = resolve(InitializeMediaDetailsQueueAction::class)->getAttachments();
    expect($attachments)->toBeEmpty();


    $this->attachment = $this->attachment->extractMediaDetailsOnQueue(true);
    ($this->action)($this->model, [$this->attachment]);


    # test
    $attachments = resolve(InitializeMediaDetailsQueueAction::class)->getAttachments();
    expect($attachments)->toBe([
        'test-id'
    ]);
});

it('respects extract-media-details-on-queue based on each attachment object', function () {
    # prepare
    $otherAttachment = clone $this->attachment;
    $otherAttachment->id = 'other-test-id';
    $otherAttachment = $otherAttachment->extractMediaDetailsOnQueue(true);

    $this->attachment = $this->attachment->extractMediaDetailsOnQueue(false);


    # action
    ($this->action)($this->model, [$this->attachment, $otherAttachment]);


    # test
    $attachments = resolve(InitializeMediaDetailsQueueAction::class)->getAttachments();

    expect($attachments)->toBe([
        'other-test-id',
    ]);
});

it('wont dispatch `DispatchModelMediaDetailsExtractionAction` when there is not file attached to the attachment', function () {
    # prepare
    $otherAttachment = clone $this->attachment;
    $otherAttachment->id = 'other-test-id';

    $this->attachment->file = false;


    # action
    ($this->action)($this->model, [$this->attachment, $otherAttachment]);


    # test
    $attachments = resolve(InitializeMediaDetailsQueueAction::class)->getAttachments();

    expect($attachments)->toBe([
        'other-test-id',
    ]);
});
