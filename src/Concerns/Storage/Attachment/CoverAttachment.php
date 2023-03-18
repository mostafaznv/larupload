<?php

namespace Mostafaznv\Larupload\Concerns\Storage\Attachment;


use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mostafaznv\Larupload\Actions\GenerateCoverFromFileAction;
use Mostafaznv\Larupload\Actions\GuessLaruploadFileTypeAction;
use Mostafaznv\Larupload\Actions\SetFileNameAction;
use Mostafaznv\Larupload\Enums\LaruploadFileType;
use Mostafaznv\Larupload\Larupload;

trait CoverAttachment
{
    public function updateCover(UploadedFile $file): bool
    {
        if ($this->output['type'] and $file->isValid()) {
            $this->uploaded = false;
            $this->cover = $file;
            $this->type = LaruploadFileType::from($this->output['type']);

            return true;
        }

        return false;
    }

    public function detachCover(): bool
    {
        if ($this->output['type']) {
            $this->uploaded = false;
            $this->cover = LARUPLOAD_NULL;
            $this->type = LaruploadFileType::from($this->output['type']);

            return true;
        }

        return false;
    }


    /**
     * Set cover photo
     * Generate cover photo automatically from photos and videos, if cover file was null
     *
     * @param $id
     */
    protected function setCover($id): void
    {
        $path = $this->getBasePath($id, Larupload::COVER_FOLDER);
        Storage::disk($this->disk)->deleteDirectory($path);

        if (isset($this->cover) and $this->cover == LARUPLOAD_NULL) {
            $this->deleteCover();
        }
        else if ($this->fileIsSetAndHasValue($this->cover) and GuessLaruploadFileTypeAction::make($this->cover)->isImage()) {
            $this->uploadCover($path);
        }
        else if ($this->generateCover) {
            $this->output = GenerateCoverFromFileAction::make(
                file: $this->file,
                disk: $this->disk,
                style: $this->coverStyle,
                withDominantColor: $this->dominantColor,
                imageProcessingLibrary: $this->imageProcessingLibrary,
                output: $this->output
            )($path);
        }
    }

    private function deleteCover(): void
    {
        $this->output['cover'] = null;

        if ($this->type != LaruploadFileType::IMAGE) {
            $this->output['dominant_color'] = null;
        }
    }

    private function uploadCover(string $path): void
    {
        Storage::disk($this->disk)->makeDirectory($path);

        $name = SetFileNameAction::make($this->cover, $this->namingMethod, $this->lang)->generate();
        $saveTo = "$path/$name";

        $result = $this->img($this->cover)->resize($saveTo, $this->coverStyle);

        if ($result) {
            $this->output['cover'] = $name;

            if ($this->type != LaruploadFileType::IMAGE) {
                $this->output['dominant_color'] = $this->dominantColor ? $this->img($this->cover)->getDominantColor() : null;
            }
        }
    }
}
