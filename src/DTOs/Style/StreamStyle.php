<?php

namespace Mostafaznv\Larupload\DTOs\Style;

use Exception;

class StreamStyle
{
    public function __construct(
        public readonly string     $name,
        public readonly int        $width,
        public readonly int        $height,
        public readonly int|string $audioBitrate,
        public readonly int|string $videoBitrate
    ) {
        $this->validate();
    }

    public static function make(
        string     $name,
        int        $width,
        int        $height,
        int|string $audioBitrate,
        int|string $videoBitrate
    ): self {
        return new self($name, $width, $height, $audioBitrate, $videoBitrate);
    }


    private function validate(): void
    {
        $this->validateName();
        $this->validateDimension();
        $this->validateBitrate('audioBitrate', $this->audioBitrate);
        $this->validateBitrate('videoBitrate', $this->videoBitrate);
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

    private function validateBitrate($attribute, $value): void
    {
        $units = ['k', 'm'];
        $value = str_ireplace($units, '', $value);

        if (!is_numeric($value)) {
            throw new Exception(
                "$attribute is not a valid bitrate"
            );
        }
    }
}
