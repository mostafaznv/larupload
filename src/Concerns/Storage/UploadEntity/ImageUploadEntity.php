<?php

namespace Mostafaznv\Larupload\Concerns\Storage\UploadEntity;


use Illuminate\Http\UploadedFile;
use Mostafaznv\Larupload\Enums\LaruploadImageLibrary;
use Mostafaznv\Larupload\Storage\Image;
use Mostafaznv\Larupload\UploadEntities;

trait ImageUploadEntity
{
    /**
     * Image instance
     */
    protected Image $image;

    protected LaruploadImageLibrary $imageProcessingLibrary;


    protected function img(UploadedFile $file): Image
    {
        $this->image = new Image(
            file: $file,
            disk: $this->disk,
            library: $this->imageProcessingLibrary,
            dominantColorQuality: $this->dominantColorQuality
        );

        return $this->image;
    }

    public function imageProcessingLibrary(LaruploadImageLibrary $library): UploadEntities
    {
        $this->imageProcessingLibrary = $library;

        return $this;
    }
}
