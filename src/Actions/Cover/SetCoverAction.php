<?php

namespace Mostafaznv\Larupload\Actions\Cover;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mostafaznv\Larupload\Actions\GuessLaruploadFileTypeAction;
use Mostafaznv\Larupload\DTOs\CoverActionData;

class SetCoverAction
{
    public function __construct(
        private readonly ?UploadedFile    $file,
        private readonly mixed           $cover,
        private readonly CoverActionData $data
    ) {}

    public static function make(?UploadedFile $file, mixed $cover, CoverActionData $data): static
    {
        return new static($file, $cover, $data);
    }


    public function run(string $path): array
    {
        Storage::disk($this->data->disk)->deleteDirectory($path);

        $output = $this->data->output;

        if ($this->shouldDeleteCover()) {
            $output = DeleteCoverAction::make($this->data->type, $output)->run();
        }
        else if ($this->shouldUploadCover()) {
            $output = UploadCoverAction::make($this->cover, $this->data)->run($path);
        }
        else if ($this->data->generateCover) {
            $output = GenerateCoverFromFileAction::make($this->file, $this->data)->run($path);
        }

        return $output;
    }

    private function shouldDeleteCover(): bool
    {
        return isset($this->cover) and $this->cover == LARUPLOAD_NULL;
    }

    private function shouldUploadCover(): bool
    {
        return file_has_value($this->cover) and GuessLaruploadFileTypeAction::make($this->cover)->isImage();
    }
}
