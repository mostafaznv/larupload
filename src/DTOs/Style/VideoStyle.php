<?php

namespace Mostafaznv\Larupload\DTOs\Style;

use Exception;
use FFMpeg\Format\Audio\Aac;
use FFMpeg\Format\Audio\Flac;
use FFMpeg\Format\Audio\Mp3;
use FFMpeg\Format\Audio\Wav;
use FFMpeg\Format\Video\Ogg;
use FFMpeg\Format\Video\WebM;
use FFMpeg\Format\Video\X264;
use Mostafaznv\Larupload\Enums\LaruploadMediaStyle;


class VideoStyle extends Style
{
    public readonly LaruploadMediaStyle            $mode;
    public readonly X264|WebM|Ogg|Mp3|Aac|Wav|Flac $format;

    public function __construct(string $name, ?int $width = null, ?int $height = null, LaruploadMediaStyle $mode = LaruploadMediaStyle::SCALE_HEIGHT, X264|WebM|Ogg|Mp3|Aac|Wav|Flac $format = new X264, bool $padding = false)
    {
        parent::__construct($name, $width, $height, $padding);

        $this->mode = $mode;
        $this->format = $format;

        $this->validateDimension();
    }

    public static function make(string $name, ?int $width = null, ?int $height = null, LaruploadMediaStyle $mode = LaruploadMediaStyle::SCALE_HEIGHT, X264|WebM|Ogg|Mp3|Aac|Wav|Flac $format = new X264, bool $padding = false): self
    {
        return new self($name, $width, $height, $mode, $format, $padding);
    }


    public function audioFormats(): array
    {
        return [
            Mp3::class, Aac::class, Wav::class, Flac::class
        ];
    }

    public function isAudioFormat(): bool
    {
        return in_array(get_class($this->format), $this->audioFormats());
    }

    public function extension(): ?string
    {
        if ($this->format instanceof WebM) {
            return 'webm';
        }
        else if ($this->format instanceof Ogg) {
            return 'ogg';
        }
        else if ($this->format instanceof Mp3) {
            return 'mp3';
        }
        else if ($this->format instanceof Aac) {
            return 'aac';
        }
        else if ($this->format instanceof Wav) {
            return 'wav';
        }
        else if ($this->format instanceof Flac) {
            return 'flac';
        }

        return 'mp4';
    }

    private function validateDimension(): void
    {
        if ($this->isAudioFormat()) {
            return;
        }

        if ($this->mode === LaruploadMediaStyle::SCALE_HEIGHT) {
            if ($this->width === null or $this->width === 0) {
                throw new Exception(
                    'Width is required when you are in SCALE_HEIGHT mode'
                );
            }
        }
        else if ($this->mode === LaruploadMediaStyle::SCALE_WIDTH) {
            if ($this->height === null or $this->height === 0) {
                throw new Exception(
                    'Height is required when you are in SCALE_WIDTH mode'
                );
            }
        }
        else if (in_array($this->mode, [LaruploadMediaStyle::CROP, LaruploadMediaStyle::FIT])) {
            if (!$this->width or !$this->height) {
                throw new Exception(
                    'Width and Height are required when you are in CROP/FIT mode'
                );
            }
        }
        else if ($this->mode === LaruploadMediaStyle::AUTO) {
            if (!$this->width and !$this->height) {
                throw new Exception(
                    'Width and height are required when you are in auto mode'
                );
            }
        }
    }
}
