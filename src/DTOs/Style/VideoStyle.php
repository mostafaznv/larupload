<?php

namespace Mostafaznv\Larupload\DTOs\Style;

use Exception;
use FFMpeg\Format\Video\X264;
use Mostafaznv\Larupload\Enums\LaruploadMediaStyle;

class VideoStyle extends Style
{
    public readonly LaruploadMediaStyle $mode;
    public readonly X264 $format;

    public function __construct(string $name, ?int $width = null, ?int $height = null, LaruploadMediaStyle $mode = LaruploadMediaStyle::SCALE_HEIGHT, X264 $format = new X264, bool $padding = false)
    {
        parent::__construct($name, $width, $height, $padding);

        $this->mode = $mode;
        $this->format = $format;

        $this->validateDimension();
    }

    public static function make(string $name, ?int $width = null, ?int $height = null, LaruploadMediaStyle $mode = LaruploadMediaStyle::SCALE_HEIGHT, X264 $format = new X264, bool $padding = false): self
    {
        return new self($name, $width, $height, $mode, $format, $padding);
    }


    private function validateDimension(): void
    {
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
