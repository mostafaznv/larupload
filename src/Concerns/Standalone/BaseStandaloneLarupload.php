<?php

namespace Mostafaznv\Larupload\Concerns\Standalone;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mostafaznv\Larupload\Actions\Attachment\CleanAttachmentAction;
use Mostafaznv\Larupload\Actions\Attachment\SaveStandaloneAttachmentAction;
use Mostafaznv\Larupload\Actions\GuessLaruploadFileTypeAction;
use Mostafaznv\Larupload\Actions\OptimizeImageAction;


trait BaseStandaloneLarupload
{
    public function upload(UploadedFile $file, ?UploadedFile $cover = null): object
    {
        $this->internalFunctionIsCallable = true;

        file_is_valid($file, $this->name, 'file');
        file_is_valid($cover, $this->name, 'cover');

        $this->file = $this->optimizeImage ? OptimizeImageAction::make($file)->process() : $file;
        $this->type = GuessLaruploadFileTypeAction::make($file)->calc();
        $this->cover = $cover;

        return SaveStandaloneAttachmentAction::make($this)->execute();
    }

    public function delete(): bool
    {
        $basePath = larupload_relative_path($this, $this->id);

        if (Storage::disk($this->disk)->exists($basePath)) {
            resolve(CleanAttachmentAction::class)($this);

            return true;
        }

        return false;
    }
}
