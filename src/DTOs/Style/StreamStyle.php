<?php

namespace Mostafaznv\Larupload\DTOs\Style;

use Exception;
use FFMpeg\Format\Video\X264;
use Mostafaznv\Larupload\Enums\LaruploadMediaStyle;

class StreamStyle
{
    public function __construct(
        public readonly string              $name,
        public readonly int                 $width,
        public readonly int                 $height,
        public readonly X264                $format,
        public readonly LaruploadMediaStyle $mode = LaruploadMediaStyle::SCALE_HEIGHT,
        public readonly bool                $padding = false
    ) {
        $this->validate();
    }

    public static function make(
        string              $name,
        int                 $width,
        int                 $height,
        X264                $format,
        LaruploadMediaStyle $mode = LaruploadMediaStyle::SCALE_HEIGHT,
        bool                $padding = false
    ): self {
        return new self($name, $width, $height, $format, $mode, $padding);
    }


    private function validate(): void
    {
        $this->validateName();
        $this->validateDimension();
    }

    private function validateName(): void
    {
        if (is_numeric($this->name)) {
            throw new Exception(
                "Style name [$this->name] is numeric. please use string name for your style"
            );
        }

        if (ctype_alnum($this->name) === false) {
            throw new Exception(
                "stream name [$this->name] should be an alpha numeric string"
            );
        }
    }

    private function validateDimension(): void
    {
        if ($this->width <= 0) {
            throw new Exception(
                "width [$this->width] should be a positive number"
            );
        }

        if ($this->height <= 0) {
            throw new Exception(
                "height [$this->height] should be a positive number"
            );
        }
    }
}
