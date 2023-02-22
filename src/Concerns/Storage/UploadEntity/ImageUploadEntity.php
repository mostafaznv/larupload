<?php

namespace Mostafaznv\Larupload\Concerns\Storage\UploadEntity;


use Exception;
use Illuminate\Http\UploadedFile;
use Mostafaznv\Larupload\LaruploadEnum;
use Mostafaznv\Larupload\Storage\Image;
use Mostafaznv\Larupload\UploadEntities;

trait ImageUploadEntity
{
    /**
     * Image instance
     */
    protected Image $image;

    protected string $imageProcessingLibrary;


    protected function image(UploadedFile $file): Image
    {
        $this->image = new Image(
            file: $file,
            disk: $this->disk,
            localDisk: $this->localDisk,
            library: $this->imageProcessingLibrary
        );

        return $this->image;
    }

    public function imageProcessingLibrary(string $library): UploadEntities
    {
        $this->validateImageProcessingLibrary($library);

        $this->imageProcessingLibrary = $library;

        return $this;
    }


    private function validateImageProcessingLibrary(string $library): void
    {
        $allowedLibraries = [
            LaruploadEnum::GD_IMAGE_LIBRARY,
            LaruploadEnum::IMAGICK_IMAGE_LIBRARY,
            LaruploadEnum::GMAGICK_IMAGE_LIBRARY
        ];

        if (!in_array($library, $allowedLibraries)) {
            $allowedLibraries = implode(', ', $allowedLibraries);

            throw new Exception(
                "Image processing library [$library] is not valid. valid libraries: [$allowedLibraries]"
            );
        }
    }
}
