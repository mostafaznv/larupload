<?php

namespace Mostafaznv\Larupload\Test\Support;

use FFMpeg\Format\Audio\Flac;
use FFMpeg\Format\Audio\Mp3;
use FFMpeg\Format\Audio\Wav;
use FFMpeg\Format\Video\Ogg;
use FFMpeg\Format\Video\WebM;
use FFMpeg\Format\Video\X264;
use Mostafaznv\Larupload\Enums\LaruploadMediaStyle;
use Mostafaznv\Larupload\Enums\LaruploadMode;
use Mostafaznv\Larupload\Storage\Attachment;

class TestAttachmentBuilder
{
    private Attachment $attachment;

    public function __construct(LaruploadMode $mode, string $disk = 'local', bool $withMeta = true)
    {
        $this->attachment = Attachment::make('main_file', $mode)
            ->disk($disk)
            ->withMeta($withMeta)
            ->dominantColor(false);
    }

    public static function make(LaruploadMode $mode, string $disk = 'local', bool $withMeta = true): self
    {
        return new static($mode, $disk, $withMeta);
    }


    # image
    public function withSmallSizeImage(): self
    {
        $this->attachment = $this->attachment->image('small_size', 200, 200, LaruploadMediaStyle::CROP);

        return $this;
    }

    public function withSmallImage(): self
    {
        $this->attachment = $this->attachment->image('small', 200, 200, LaruploadMediaStyle::CROP);

        return $this;
    }

    public function withMediumImage(): self
    {
        $this->attachment = $this->attachment->image('medium', 800, 800, LaruploadMediaStyle::AUTO);

        return $this;
    }

    public function withLandscapeImage(): self
    {
        $this->attachment = $this->attachment->image('landscape', 400, null, LaruploadMediaStyle::SCALE_HEIGHT);

        return $this;
    }

    public function withPortraitImage(): self
    {
        $this->attachment = $this->attachment->image('portrait', null, 400, LaruploadMediaStyle::SCALE_WIDTH);

        return $this;
    }

    public function withExactImage(): self
    {
        $this->attachment = $this->attachment->image('exact', 300, 190, LaruploadMediaStyle::FIT);

        return $this;
    }

    public function withAutoImage(): self
    {
        $this->attachment = $this->attachment->image('auto', 300, 190, LaruploadMediaStyle::AUTO);

        return $this;
    }

    public function withAllImages(): self
    {
        $this->withSmallSizeImage()
            ->withSmallImage()
            ->withMediumImage()
            ->withLandscapeImage()
            ->withPortraitImage()
            ->withExactImage()
            ->withAutoImage();

        return $this;
    }


    # audio
    public function withMp3Audio(): self
    {
        $this->attachment = $this->attachment->audio('audio_mp3', new Mp3);

        return $this;
    }

    public function withWavAudio(): self
    {
        $this->attachment = $this->attachment->audio('audio_wav', new Wav);

        return $this;
    }

    public function withFlacAudio(): self
    {
        $this->attachment = $this->attachment->audio('audio_flac', new Flac);

        return $this;
    }

    public function withAllAudios(): self
    {
        $this->withMp3Audio()
            ->withWavAudio()
            ->withFlacAudio();

        return $this;
    }


    # video
    public function withSmallSizeVideo(): self
    {
        $this->attachment = $this->attachment->video('small_size', 200, 200, LaruploadMediaStyle::CROP);

        return $this;
    }

    public function withSmallVideo(): self
    {
        $this->attachment = $this->attachment->video('small', 200, 200, LaruploadMediaStyle::CROP);

        return $this;
    }

    public function withMediumVideo(): self
    {
        $this->attachment = $this->attachment->video('medium', 800, 800, LaruploadMediaStyle::AUTO, new X264, true);

        return $this;
    }

    public function withLandscapeVideo(): self
    {
        $this->attachment = $this->attachment->video('landscape', 400, null, LaruploadMediaStyle::SCALE_HEIGHT);

        return $this;
    }

    public function withPortraitVideo(): self
    {
        $this->attachment = $this->attachment->video('portrait', null, 400, LaruploadMediaStyle::SCALE_WIDTH);

        return $this;
    }

    public function withExactVideo(): self
    {
        $this->attachment = $this->attachment->video('exact', 300, 190, LaruploadMediaStyle::FIT);

        return $this;
    }

    public function withAutoVideo(): self
    {
        $this->attachment = $this->attachment->video(
            name: 'auto',
            width: 300,
            height: 190,
            mode: LaruploadMediaStyle::AUTO,
            format: (new X264)
                ->setKiloBitrate(1000)
                ->setAudioKiloBitrate(64)
        );

        return $this;
    }

    public function withWebmVideo(): self
    {
        $this->attachment = $this->attachment->video(
            name: 'webm',
            width: 400,
            format: new WebM
        );

        return $this;
    }

    public function withOggVideo(): self
    {
        $this->attachment = $this->attachment->video(
            name: 'ogg',
            width: 400,
            format: new Ogg
        );

        return $this;
    }

    public function withMp3Video(): self
    {
        $this->attachment = $this->attachment->video(
            name: 'mp3',
            format: new Mp3
        );

        return $this;
    }

    public function withWavVideo(): self
    {
        $this->attachment = $this->attachment->video(
            name: 'wav',
            format: new Wav
        );

        return $this;
    }

    public function withFlacVideo(): self
    {
        $this->attachment = $this->attachment->video(
            name: 'flac',
            format: new Flac
        );

        return $this;
    }

    public function withAllVideos(): self
    {
        $this->withSmallSizeVideo()
            ->withSmallVideo()
            ->withMediumVideo()
            ->withLandscapeVideo()
            ->withPortraitVideo()
            ->withExactVideo()
            ->withAutoVideo();

        return $this;
    }

    public function withAllCustomFormatVideos(): self
    {
        $this->withOggVideo()
            ->withMp3Video()
            ->withWavVideo()
            ->withWebmVideo()
            ->withFlacVideo();

        return $this;
    }

    public function with480pStream(): self
    {
        $this->attachment = $this->attachment->stream(
            name: '480p',
            width: 640,
            height: 480,
            format: (new X264)
                ->setKiloBitrate(3000)
                ->setAudioKiloBitrate(64)
        );

        return $this;
    }

    public function with720pStream(): self
    {
        $this->attachment = $this->attachment->stream(
            name: '720p',
            width: 1280,
            height: 720,
            format: (new X264)
                ->setKiloBitrate(1000)
                ->setAudioKiloBitrate(64)
        );

        return $this;
    }

    public function withStreams(): self
    {
        $this->with480pStream()->with720pStream();

        return $this;
    }

    public function withAllVideosAndStreams(): self
    {
        $this->withAllVideos()->withStreams();

        return $this;
    }


    # generic
    public function withAll(): self
    {
        $this->withAllImages()->withAllVideosAndStreams()->withAllAudios();

        return $this;
    }

    public function toObject(): Attachment
    {
        return $this->attachment;
    }

    public function toArray(): array
    {
        return [$this->toObject()];
    }

    public function calculateDominantColor(bool $status = true): self
    {
        $this->attachment = $this->attachment->dominantColor($status);

        return $this;
    }

    public function dominantColorQuality(int $quality): self
    {
        $this->attachment = $this->attachment->dominantColorQuality($quality);

        return $this;
    }
}
