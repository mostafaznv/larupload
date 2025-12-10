<?php

namespace Mostafaznv\Larupload\Concerns\Storage\Attachment;

use Illuminate\Http\UploadedFile;
use Mostafaznv\Larupload\Actions\GuessLaruploadFileTypeAction;
use Mostafaznv\Larupload\Actions\OptimizeImageAction;
use Mostafaznv\Larupload\Enums\LaruploadFileType;


trait AttachmentActions
{
    public function attach(UploadedFile|false $file, ?UploadedFile $cover = null): void
    {
        file_is_valid($file, $this->name, 'file');
        file_is_valid($cover, $this->name, 'cover');

        $this->file = $file;
        $this->uploaded = false;


        if ($file === false) {
            $this->cover = null;
        }
        else {
            $this->cover = $cover;
            $this->type = GuessLaruploadFileTypeAction::make($file)->calc();

            if ($this->type === LaruploadFileType::IMAGE && $this->optimizeImage) {
                $this->file = OptimizeImageAction::make($file)->process();
            }
        }
    }

    public function detach(): void
    {
        $this->attach(false);
    }
}
