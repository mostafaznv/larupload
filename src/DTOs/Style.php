<?php

namespace Mostafaznv\Larupload\DTOs;

use Exception;
use Mostafaznv\Larupload\Enums\LaruploadStyleMode;
use Mostafaznv\Larupload\Enums\LaruploadStyleType;

class Style
{
    public function __construct(
        public readonly string              $name,
        public readonly ?int                $width = null,
        public readonly ?int                $height = null,
        public readonly ?LaruploadStyleMode $mode = null,
        public readonly array               $type = [],
    ) {
        $this->validate();
    }

    public static function make(
        string              $name,
        ?int                $width = null,
        ?int                $height = null,
        ?LaruploadStyleMode $mode = null,
        array               $type = []
    ): self {
        return new self($name, $width, $height, $mode, $type);
    }


    private function validate(): void
    {
        $this->validateName();
        $this->validateDimension();
        $this->validateType();
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
        if ($this->mode === LaruploadStyleMode::CROP) {
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
            $types = enum_to_names($this->type);
            $availableTypes = enum_to_names(LaruploadStyleType::cases());


            if (count(array_intersect($types, $availableTypes)) != count($types)) {
                $types = implode(', ', $types);
                $availableTypes = implode(', ', $availableTypes);

                throw new Exception(
                    "Style type [$types] is not valid. valid types: [$availableTypes]"
                );
            }
        }
    }
}
