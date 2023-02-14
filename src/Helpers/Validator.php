<?php

namespace Mostafaznv\Larupload\Helpers;

use Exception;
use Mostafaznv\Larupload\LaruploadEnum;

class Validator
{
    /**
     * Validate naming methods
     *
     * @param string $value
     * @throws Exception
     */
    public static function namingMethodIsValid(string $value): void
    {
        $namingMethods = [LaruploadEnum::SLUG_NAMING_METHOD, LaruploadEnum::HASH_FILE_NAMING_METHOD, LaruploadEnum::TIME_NAMING_METHOD];

        if (!in_array($value, $namingMethods)) {
            throw new Exception("Naming method [$value] is not valid. valid methods: [" . implode(', ', $namingMethods) . "]");
        }
    }

    /**
     * Validate image processing library
     *
     * @param string $value
     * @throws Exception
     */
    public static function imageProcessingLibraryIsValid(string $value): void
    {
        $imageLibrary = [LaruploadEnum::GD_IMAGE_LIBRARY, LaruploadEnum::IMAGICK_IMAGE_LIBRARY, LaruploadEnum::GMAGICK_IMAGE_LIBRARY];

        if (!in_array($value, $imageLibrary)) {
            throw new Exception("Image processing library [$value] is not valid. valid libraries: [" . implode(', ', $imageLibrary) . "]");
        }
    }
}
