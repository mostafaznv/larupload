<?php

namespace Mostafaznv\Larupload\Actions;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mostafaznv\Larupload\DTOs\Style\ImageStyle;
use Mostafaznv\Larupload\Enums\LaruploadFileType;
use Mostafaznv\Larupload\Enums\LaruploadImageLibrary;
use Mostafaznv\Larupload\Enums\LaruploadNamingMethod;
use Mostafaznv\Larupload\Storage\Image;

class UploadCoverAction
{
    private readonly ?LaruploadFileType $type;

    public function __construct(
        private readonly UploadedFile          $cover,
        private readonly string                $disk,
        private readonly LaruploadNamingMethod $namingMethod,
        private readonly ?string               $lang,
        private readonly ImageStyle            $style,
        private readonly bool                  $withDominantColor,
        private readonly LaruploadImageLibrary $imageProcessingLibrary,
        private array                          $output
    ) {
        $this->type = GuessLaruploadFileTypeAction::make($this->cover)();
    }

    public static function make(UploadedFile $cover, string $disk, LaruploadNamingMethod $namingMethod, string $lang, ImageStyle $style, bool $withDominantColor, LaruploadImageLibrary $imageProcessingLibrary, array $output): self
    {
        return new self($cover, $disk, $namingMethod, $lang, $style, $withDominantColor, $imageProcessingLibrary, $output);
    }


    public function __invoke(string $path): array
    {
        Storage::disk($this->disk)->makeDirectory($path);

        $img = $this->img();
        $name = SetFileNameAction::make($this->cover, $this->namingMethod, $this->lang)->generate();
        $saveTo = "$path/$name";

        $result = $img->resize($saveTo, $this->style);

        if ($result) {
            $this->output['cover'] = $name;

            if ($this->type != LaruploadFileType::IMAGE) {
                $this->output['dominant_color'] = $this->withDominantColor ? $img->getDominantColor() : null;
            }
        }

        return $this->output;
    }

    private function img(): Image
    {
        return new Image(
            file: $this->cover,
            disk: $this->disk,
            library: $this->imageProcessingLibrary
        );
    }
}
