<?php

namespace Mostafaznv\Larupload\Concerns\Storage\UploadEntity;


use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Mostafaznv\Larupload\UploadEntities;
use Symfony\Component\HttpFoundation\StreamedResponse;

trait UploadEntityResponse
{
    /**
     * Specify whether Larupload should return meta values on getAttribute or not.
     */
    protected bool $withMeta;

    /**
     * Specify whether Larupload should return responses in camel-case or not.
     */
    protected bool $camelCaseResponse;


    public function withMeta(bool $status): UploadEntities
    {
        $this->withMeta = $status;

        return $this;
    }

    protected function storageUrl(string $path): ?string
    {
        if (isset($this->file) and $this->file === false) {
            return null;
        }

        if ($this->driverIsLocal()) {
            $url = Storage::disk($this->disk)->url($path);

            return url($url);
        }

        $baseUrl = config("filesystems.disks.$this->disk.url");

        if ($baseUrl) {
            return "$baseUrl/$path";
        }

        return $path;
    }

    protected function storageDownload(string $path): StreamedResponse|RedirectResponse|null
    {
        if (isset($this->file) and $this->file === false) {
            return null;
        }

        if ($this->driverIsLocal()) {
            return Storage::disk($this->disk)->download($path);
        }

        $baseUrl = config("filesystems.disks.$this->disk.url");

        if ($baseUrl) {
            return redirect("$baseUrl/$path");
        }

        return null;
    }
}
