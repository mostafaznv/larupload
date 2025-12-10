<?php

namespace Mostafaznv\Larupload\Traits;

use Illuminate\Http\UploadedFile;
use Mostafaznv\Larupload\Storage\Image;


trait HasImage
{
    protected Image $image;


    protected function img(UploadedFile $file): Image
    {
        $this->image = new Image(
            file: $file,
            disk: $this->attachment->disk,
            library: $this->attachment->imageProcessingLibrary,
            dominantColorQuality: $this->attachment->dominantColorQuality
        );

        return $this->image;
    }
}
