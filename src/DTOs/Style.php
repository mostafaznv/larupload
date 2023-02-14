<?php

namespace Mostafaznv\Larupload\DTOs;

use Exception;
use Mostafaznv\Larupload\LaruploadEnum;

class Style
{
    public function __construct(
        public readonly string  $name,
        public readonly ?int    $width = null,
        public readonly ?int    $height = null,
        public readonly ?string $mode = null,
        public readonly array   $type = [],
    ) {
        $this->validate();
    }

    public static function make(
        string  $name,
        ?int    $width = null,
        ?int    $height = null,
        ?string $mode = null,
        array   $type = []
    ): self {
        return new self($name, $width, $height, $mode, $type);
    }


    private function validate(): void
    {
        $this->validateMode();
        $this->validateName();
        $this->validateDimension();
        $this->validateType();
    }

    private function validateMode(): void
    {
        if ($this->mode) {
            $availableModes = [
                LaruploadEnum::LANDSCAPE_STYLE_MODE, LaruploadEnum::PORTRAIT_STYLE_MODE,
                LaruploadEnum::CROP_STYLE_MODE, LaruploadEnum::EXACT_STYLE_MODE, LaruploadEnum::AUTO_STYLE_MODE
            ];

            if (!in_array($this->mode, $availableModes)) {
                $availableModes = implode(', ', $availableModes);

                throw new Exception(
                    "Style mode [$this->mode] is not valid. valid modes: [$availableModes]"
                );
            }
        }
    }

    private function validateName(): void
    {
        if (is_numeric($this->name)) {
            throw new Exception(
                "Style name [$this->name] is numeric. please use string name for your style"
            );
        }
    }

    private function validateDimension(): void
    {
        if ($this->mode == LaruploadEnum::CROP_STYLE_MODE) {
            if ($this->height === null or $this->height === 0) {
                throw new Exception(
                    'Height is required when you are in crop mode'
                );
            }

            if ($this->width === null or $this->width === 0) {
                throw new Exception(
                    'Width is required when you are in crop mode'
                );
            }
        }
    }

    private function validateType(): void
    {
        if (!empty($this->type)) {
            $availableTypes = [
                LaruploadEnum::IMAGE_STYLE_TYPE, LaruploadEnum::VIDEO_STYLE_TYPE
            ];

            if (count(array_intersect($this->type, $availableTypes)) != count($availableTypes)) {
                $type = implode(', ', $this->type);
                $availableTypes = implode(', ', $availableTypes);

                throw new Exception(
                    "Style type [$type] is not valid. valid types: [$availableTypes]"
                );
            }
        }
    }
}