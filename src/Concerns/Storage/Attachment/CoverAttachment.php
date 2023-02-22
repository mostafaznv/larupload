<?php

namespace Mostafaznv\Larupload\Concerns\Storage\Attachment;


use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mostafaznv\Larupload\LaruploadEnum;

trait CoverAttachment
{
    public function updateCover(UploadedFile $file): bool
    {
        if ($this->output['type'] and $file->isValid()) {
            $this->uploaded = false;
            $this->cover = $file;
            $this->type = $this->output['type'];

            return true;
        }
        else {
            return false;
        }
    }

    public function detachCover(): bool
    {
        if ($this->output['type']) {
            $this->uploaded = false;
            $this->cover = LARUPLOAD_NULL;
            $this->type = $this->output['type'];

            return true;
        }
        else {
            return false;
        }
    }


    /**
     * Set cover photo
     * Generate cover photo automatically from photos and videos, if cover file was null
     *
     * @param $id
     */
    protected function setCover($id): void
    {
        $path = $this->getBasePath($id, LaruploadEnum::COVER_FOLDER);
        Storage::disk($this->disk)->deleteDirectory($path);

        if (isset($this->cover) and $this->cover == LARUPLOAD_NULL) {
            $this->deleteCover();
        }
        else if ($this->fileIsSetAndHasValue($this->cover) and $this->isImage($this->cover)) {
            $this->uploadCover($path);
        }
        else {
            $this->generateCoverFromOriginalFile($path);
        }
    }

    private function deleteCover(): void
    {
        $this->output['cover'] = null;

        if ($this->type != LaruploadEnum::IMAGE) {
            $this->output['dominant_color'] = null;
        }
    }

    private function uploadCover(string $path): void
    {
        Storage::disk($this->disk)->makeDirectory($path);

        $name = $this->setFileName($this->cover);
        $saveTo = "$path/$name";

        $result = $this->image($this->cover)->resize($saveTo, $this->coverStyle);

        if ($result) {
            $this->output['cover'] = $name;

            if ($this->type != LaruploadEnum::IMAGE) {
                $this->output['dominant_color'] = $this->dominantColor ? $this->image($this->cover)->getDominantColor() : null;
            }
        }
    }

    private function generateCoverFromOriginalFile(string $path): void
    {
        if (!$this->generateCover) {
            return;
        }

        Storage::disk($this->disk)->makeDirectory($path);

        $fileName = pathinfo($this->output['name'], PATHINFO_FILENAME);
        $format = $this->type == LaruploadEnum::IMAGE ? ($this->output['format'] == 'svg' ? 'png' : $this->output['format']) : 'jpg';
        $name = "$fileName.$format";
        $saveTo = "$path/$name";

        switch ($this->type) {
            case LaruploadEnum::VIDEO:
                Storage::disk($this->disk)->makeDirectory($path);

                $color = $this->ffmpeg()->capture($this->ffmpegCaptureFrame, $this->coverStyle, $saveTo, $this->dominantColor);

                $this->output['cover'] = $name;
                $this->output['dominant_color'] = $this->dominantColor ? $color : null;

                break;

            case LaruploadEnum::IMAGE:
                Storage::disk($this->disk)->makeDirectory($path);

                $result = $this->image($this->file)->resize($saveTo, $this->coverStyle);

                if ($result) {
                    $this->output['cover'] = $name;
                }

                break;
        }
    }
}
