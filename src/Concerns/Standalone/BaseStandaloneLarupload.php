<?php

namespace Mostafaznv\Larupload\Concerns\Standalone;


use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mostafaznv\Larupload\Actions\GuessLaruploadFileTypeAction;
use Mostafaznv\Larupload\Actions\OptimizeImageAction;

trait BaseStandaloneLarupload
{
    public function upload(UploadedFile $file, UploadedFile $cover = null): object
    {
        $this->internalFunctionIsCallable = true;

        file_is_valid($file, $this->name, 'file');
        file_is_valid($cover, $this->name, 'cover');

        $this->file = $this->optimizeImage ? OptimizeImageAction::make($file)->process() : $file;
        $this->type = GuessLaruploadFileTypeAction::make($file)->calc();
        $this->cover = $cover;

        $this->clean($this->id);
        $this->setBasicDetails();
        $this->setMediaDetails();
        $this->uploadOriginalFile($this->id);
        $this->setCover($this->id);
        $this->handleStyles($this->id, self::class, true);
        $urls = $this->urls();

        $this->updateMeta($urls);

        return $urls;
    }

    public function delete(): bool
    {
        $basePath = $this->getBasePath($this->id);

        if (Storage::disk($this->disk)->exists($basePath)) {
            $this->clean($this->id);

            return true;
        }

        return false;
    }

    private function updateMeta(object $urls = null): void
    {
        if (is_null($urls)) {
            $urls = $this->urls();
        }

        $metaPath = $this->getBasePath($this->id) . '/.meta';
        Storage::disk($this->disk)->put($metaPath, json_encode($urls), 'private');
    }

    /**
     * Check if .meta file exists
     *
     * @return bool
     */
    private function metaExists(): bool
    {
        $metaPath = $this->getBasePath($this->id) . '/.meta';

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
