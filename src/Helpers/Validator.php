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

    /**
     * Validate style
     *
     * @param string $name
     * @param array $type
     * @param string|null $mode
     * @param int|null $width
     * @param int|null $height
     * @throws Exception
     */
    public static function styleIsValid(string $name, array $type = [], string $mode = null, int $width = null, int $height = null): void
    {
        self::modeIsValid($mode);

        // validate name
        if (is_numeric($name)) {
            throw new Exception("Style name [$name] is numeric. please use string name for your style");
        }

        // validate width and height
        if ($mode == LaruploadEnum::CROP_STYLE_MODE) {
            if (!$height) {
                throw new Exception('Height is required when you are in crop mode');
            }

            if (!$width) {
                throw new Exception('Width is required when you are in crop mode');
            }
        }

        // validate type
        if ($type) {
            $types = [LaruploadEnum::IMAGE_STYLE_TYPE, LaruploadEnum::VIDEO_STYLE_TYPE];

            if (count(array_intersect($type, $types)) != count($type)) {
                throw new Exception('Style type [' . implode(', ', $type) . '] is not valid. valid types: [' . implode(', ', $types) . ']');
            }
        }
    }

    /**
     * Validate stream styles
     *
     * @param int|string $audioBitrate
     * @param int|string $videoBitrate
     * @throws Exception
     */
    public static function streamIsValid(int|string $audioBitrate, int|string $videoBitrate): void
    {
        self::numericBitrateRule('audioBitrate', $audioBitrate);
        self::numericBitrateRule('videoBitrate', $videoBitrate);
    }

    /**
     * Validate mode
     *
     * @param string|null $mode
     * @throws Exception
     */
    public static function modeIsValid(string $mode = null): void
    {
        if ($mode) {
            $modes = [
                LaruploadEnum::LANDSCAPE_STYLE_MODE, LaruploadEnum::PORTRAIT_STYLE_MODE, LaruploadEnum::CROP_STYLE_MODE,
                LaruploadEnum::EXACT_STYLE_MODE, LaruploadEnum::AUTO_STYLE_MODE
            ];

            if (!in_array($mode, $modes)) {
                throw new Exception("Style mode [$mode] is not valid. valid modes: [" . implode(', ', $modes) . "]");
            }
        }
    }

    /**
     * Validate Bitrate
     *
     * @param $attribute
     * @param $value
     * @throws Exception
     */
    protected static function numericBitrateRule($attribute, $value): void
    {
        $units = ['k', 'm'];
        $value = str_ireplace($units, '', $value);

        if (!is_numeric($value)) {
            throw new Exception($attribute . ' is not a valid bitrate');
        }
    }
}
