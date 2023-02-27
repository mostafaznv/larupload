<?php

namespace Mostafaznv\Larupload\DTOs\Style;

use Exception;

abstract class Style
{
    public function __construct(
        public readonly string $name,
        public readonly ?int   $width = null,
        public readonly ?int   $height = null,
        public readonly bool   $padding = false
    ) {
        $this->validate();
    }


    private function validate(): void
    {
        $this->validateName();
    }

    private function validateName(): void
    {
        if (is_numeric($this->name)) {
            throw new Exception(
                "Style name [$this->name] is numeric. please use string name for your style"
            );
        }
    }
}
