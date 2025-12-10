<?php

namespace Mostafaznv\Larupload\DTOs\FFMpeg;


readonly class FFMpegStreamRepresentation
{
    public function __construct(
        public string $name,
        public string $path,
        public string $listName,
    ) {}

    public static function make(string $name, string $path, string $listName): self
    {
        return new self($name, $path, $listName);
    }
}
