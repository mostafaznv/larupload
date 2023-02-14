<?php

namespace Mostafaznv\Larupload\Concerns\Standalone;


use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait BaseStandaloneLarupload
{
    public function upload(UploadedFile $file, UploadedFile $cover = null): object
    {
        $this->internalFunctionIsCallable = true;

        $this->file = $file;
        $this->type = $this->getFileType($file);
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

    protected function updateMeta(object $urls = null): void
    {
        if (is_null($urls)) {
            $urls = $this->urls();
        }

        $metaPath = $this->getBasePath($this->id) . '/.meta';
        Storage::disk($this->disk)->put($metaPath, json_encode($urls), 'private');
    }

    /**
     * Check if .meta is exists or not
     *
     * @return bool
     */
    protected function metaIsExists(): bool
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
