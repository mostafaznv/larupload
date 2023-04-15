<?php

namespace Mostafaznv\Larupload\Actions\Cover;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mostafaznv\Larupload\Actions\SetFileNameAction;
use Mostafaznv\Larupload\DTOs\CoverActionData;
use Mostafaznv\Larupload\Enums\LaruploadFileType;
use Mostafaznv\Larupload\Storage\Image;

class UploadCoverAction
{
    private array $output;

    public function __construct(private readonly UploadedFile $cover, private readonly CoverActionData $data)
    {
        $this->output = $this->data->output;
    }

    public static function make(UploadedFile $cover, CoverActionData $data): static
    {
        return new static($cover, $data);
    }


    public function run(string $path): array
    {
        Storage::disk($this->data->disk)->makeDirectory($path);

        $img = $this->img();
        $name = SetFileNameAction::make($this->cover, $this->data->namingMethod, $this->data->lang)->generate();
        $saveTo = "$path/$name";

        $result = $img->resize($saveTo, $this->data->style);

        if ($result) {
            $this->output['cover'] = $name;

            if ($this->data->type != LaruploadFileType::IMAGE) {
                $this->output['dominant_color'] = $this->data->withDominantColor ? $img->getDominantColor() : null;
            }
        }

        return $this->output;
    }

    private function img(): Image
    {
        return new Image(
            file: $this->cover,
            disk: $this->data->disk,
            library: $this->data->imageProcessingLibrary,
            dominantColorQuality: $this->data->dominantColorQuality
        );
    }
}
