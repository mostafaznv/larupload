<?php

namespace Mostafaznv\Larupload\Concerns\Storage\Attachment;


use Illuminate\Http\UploadedFile;
use Mostafaznv\Larupload\Actions\Cover\SetCoverAction;
use Mostafaznv\Larupload\DTOs\CoverActionData;
use Mostafaznv\Larupload\Enums\LaruploadFileType;
use Mostafaznv\Larupload\Larupload;

trait CoverAttachment
{
    public function updateCover(UploadedFile $file): bool
    {
        file_is_valid($file, $this->name, 'cover');

        if ($this->output['type']) {
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
