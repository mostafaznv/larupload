<?php

namespace Mostafaznv\Larupload\DTOs;

use Mostafaznv\Larupload\DTOs\Style\ImageStyle;
use Mostafaznv\Larupload\Enums\LaruploadFileType;
use Mostafaznv\Larupload\Enums\LaruploadImageLibrary;
use Mostafaznv\Larupload\Enums\LaruploadNamingMethod;


class CoverActionData
{
    public function __construct(
        public readonly string                $disk,
        public readonly LaruploadNamingMethod $namingMethod,
        public readonly ?string               $lang,
        public readonly ImageStyle            $style,
        public readonly ?LaruploadFileType    $type,
        public readonly bool                  $generateCover,
        public readonly bool                  $withDominantColor,
        public readonly int                   $dominantColorQuality,
        public readonly LaruploadImageLibrary $imageProcessingLibrary,
        public array                          $output
    ) {}

    public static function make(string $disk, LaruploadNamingMethod $namingMethod, string $lang, ImageStyle $style, ?LaruploadFileType $type, bool $generateCover, bool $withDominantColor, int $dominantColorQuality, LaruploadImageLibrary $imageProcessingLibrary, array $output): static
    {
        return new static($disk, $namingMethod, $lang, $style, $type, $generateCover, $withDominantColor, $dominantColorQuality, $imageProcessingLibrary, $output);
    }
}
