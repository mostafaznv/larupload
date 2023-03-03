<?php

namespace Mostafaznv\Larupload\DTOs\Style;

use Exception;
use Mostafaznv\Larupload\Enums\LaruploadMediaStyle;

class StreamStyle
{
    public function __construct(
        public readonly string              $name,
        public readonly int                 $width,
        public readonly int                 $height,
        public readonly int                 $audioKiloBitrate,
        public readonly int                 $videoKiloBitrate,
        public readonly LaruploadMediaStyle $mode = LaruploadMediaStyle::SCALE_HEIGHT,
        public readonly bool                $padding = false
    ) {
        $this->validate();
    }

    public static function make(
        string              $name,
        int                 $width,
        int                 $height,
        int                 $audioKiloBitrate,
        int                 $videoKiloBitrate,
        LaruploadMediaStyle $mode = LaruploadMediaStyle::SCALE_HEIGHT,
        bool                $padding = false
    ): self {
        return new self($name, $width, $height, $audioKiloBitrate, $videoKiloBitrate, $mode, $padding);
    }


    private function validate(): void
    {
        $this->validateName();
        $this->validateDimension();
    }

    private function validateName(): void
    {
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
