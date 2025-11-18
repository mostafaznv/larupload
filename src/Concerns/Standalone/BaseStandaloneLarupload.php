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

    private function updateMeta(?object $urls = null): void
    {
        if (is_null($urls)) {
            $urls = $this->urls();
        }

        $metaPath = larupload_relative_path($this, $this->id) . '/.meta';
        Storage::disk($this->disk)->put($metaPath, json_encode($urls), 'private');
    }

    /**
     * Check if .meta file exists
     *
     * @return bool
     */
    private function metaExists(): bool
    {
        $metaPath = larupload_relative_path($this, $this->id) . '/.meta';

        if (Storage::disk($this->disk)->exists($metaPath)) {
            $meta = Storage::disk($this->disk)->get($metaPath);
            $meta = json_decode($meta);

            foreach ($meta->meta as $key => $value) {
                $this->output[$key] = $value;
            }

            return true;
        }

        return false;
    }
}
