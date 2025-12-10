<?php

namespace Mostafaznv\Larupload\Concerns\Storage\Attachment;

use Illuminate\Http\UploadedFile;
use Mostafaznv\Larupload\Actions\Cover\SetCoverAction;
use Mostafaznv\Larupload\DTOs\CoverActionData;
use Mostafaznv\Larupload\Larupload;


trait CoverAttachment
{
    public function updateCover(UploadedFile $file): bool
    {
        file_is_valid($file, $this->name, 'cover');

        if ($this->output->type) {
            $this->uploaded = false;
            $this->cover = $file;
            $this->type = $this->output->type;

            return true;
        }

        return false;
    }

    public function detachCover(): bool
    {
        if ($this->output->type) {
            $this->uploaded = false;
            $this->cover = false;
            $this->type = $this->output->type;

            return true;
        }

        return false;
    }

    public function setCover($id): void
    {
        $path = larupload_relative_path($this, $id, Larupload::COVER_FOLDER);
        $data = CoverActionData::make(
            disk: $this->disk,
            namingMethod: $this->namingMethod,
            lang: $this->lang,
            style: $this->coverStyle,
            type: $this->type,
            generateCover: $this->generateCover,
            withDominantColor: $this->dominantColor,
            dominantColorQuality: $this->dominantColorQuality,
            imageProcessingLibrary: $this->imageProcessingLibrary,
            output: $this->output
        );

        $this->output = SetCoverAction::make($this->file ?? null, $this->cover, $data)->run($path);
    }
}
