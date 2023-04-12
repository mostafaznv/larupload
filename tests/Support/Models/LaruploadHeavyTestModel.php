<?php

namespace Mostafaznv\Larupload\Test\Support\Models;

use FFMpeg\Format\Video\X264;
use Illuminate\Database\Eloquent\Model;
use Mostafaznv\Larupload\Enums\LaruploadMediaStyle;
use Mostafaznv\Larupload\Enums\LaruploadMode;
use Mostafaznv\Larupload\Enums\LaruploadNamingMethod;
use Mostafaznv\Larupload\Storage\Attachment;
use Mostafaznv\Larupload\Traits\Larupload;

class LaruploadHeavyTestModel extends Model
{
    use Larupload;

    protected $table = 'upload_heavy';

    protected $fillable = [
        'main_file'
    ];

    public function attachments(): array
    {
        return [
            Attachment::make('main_file', LaruploadMode::HEAVY)
                ->disk('local')
                ->withMeta(true)
                ->image('small_size', 200, 200, LaruploadMediaStyle::CROP)
                ->image('small', 200, 200, LaruploadMediaStyle::CROP)
                ->image('medium', 800, 800, LaruploadMediaStyle::AUTO)
                ->image('landscape', 400, null, LaruploadMediaStyle::SCALE_HEIGHT)
                ->image('portrait', null, 400, LaruploadMediaStyle::SCALE_WIDTH)
                ->image('exact', 300, 190, LaruploadMediaStyle::FIT)
                ->image('auto', 300, 190, LaruploadMediaStyle::AUTO)
                ->video('small_size', 200, 200, LaruploadMediaStyle::CROP)
                ->video('small', 200, 200, LaruploadMediaStyle::CROP)
                ->video('medium', 800, 800, LaruploadMediaStyle::AUTO)
                ->video('landscape', 400, null, LaruploadMediaStyle::SCALE_HEIGHT)
                ->video('portrait', null, 400, LaruploadMediaStyle::SCALE_WIDTH)
                ->video('exact', 300, 190, LaruploadMediaStyle::FIT)
                ->video(
                    name: 'auto',
                    width: 300,
                    height: 190,
                    mode: LaruploadMediaStyle::AUTO,
                    format: (new X264)
                        ->setKiloBitrate(1000)
                        ->setAudioKiloBitrate(64)
                )
                ->stream(
                    name: '480p',
                    width: 640,
                    height: 480,
                    format: (new X264)
                        ->setKiloBitrate(3000)
                        ->setAudioKiloBitrate(64)
                )
                ->stream(
                    name: '720p',
                    width: 1280,
                    height: 720,
                    format: (new X264)
                        ->setKiloBitrate(1000)
                        ->setAudioKiloBitrate(64)
                )
        ];
    }

    public function setAttachments(array $attachments): void
    {
        $this->attachments = $attachments;
    }
}
