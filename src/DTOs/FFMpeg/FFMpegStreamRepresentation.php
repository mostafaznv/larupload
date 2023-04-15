<?php

namespace Mostafaznv\Larupload\DTOs\FFMpeg;

class FFMpegStreamRepresentation
{
    public function __construct(
        public readonly string $name,
        public readonly string $path,
        public readonly string $listName,
    ) {}

    public static function make(string $name, string $path, string $listName): self
    {
        return new self($name, $path, $listName);
    }
}
