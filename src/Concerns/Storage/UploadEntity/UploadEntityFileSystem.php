<?php

namespace Mostafaznv\Larupload\Concerns\Storage\UploadEntity;


use Illuminate\Support\Str;
use Mostafaznv\Larupload\UploadEntities;

trait UploadEntityFileSystem
{
    protected string $folder = '';

    protected string $disk;

    protected string $localDisk;


    public function folder(string $name): UploadEntities
    {
        if (!$this->folder) {
            $this->folder = str_replace('_', '-', Str::kebab($name));
        }

        return $this;
    }

    public function disk(string $disk): UploadEntities
    {
        $this->disk = $disk;

        return $this;
    }

    protected function driverIsLocal(): bool
    {
        return $this->diskDriverIsLocal($this->disk);
    }

    protected function driverIsNotLocal(): bool
    {
        return !$this->driverIsLocal();
    }
}
