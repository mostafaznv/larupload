<?php

namespace Mostafaznv\Larupload;

use Exception;

class LaruploadEnum
{
    const HEAVY_MODE = 'heavy';
    const LIGHT_MODE = 'light';

    const SLUG_NAMING_METHOD      = 'slug';
    const HASH_FILE_NAMING_METHOD = 'hash_file';
    const TIME_NAMING_METHOD      = 'time';

    const GD_IMAGE_LIBRARY      = 'Imagine\Gd\Imagine';
    const IMAGICK_IMAGE_LIBRARY = 'Imagine\Imagick\Imagine';
    const GMAGICK_IMAGE_LIBRARY = 'Imagine\Gmagick\Imagine';

    const LANDSCAPE_STYLE_MODE = 'landscape';
    const PORTRAIT_STYLE_MODE  = 'portrait';
    const CROP_STYLE_MODE      = 'crop';
    const EXACT_STYLE_MODE     = 'exact';
    const AUTO_STYLE_MODE      = 'auto';

    const IMAGE_STYLE_TYPE = 'image';
    const VIDEO_STYLE_TYPE = 'video';


    /**
     * Validate naming methods
     *
     * @param string $value
     * @throws Exception
     */
    public static function namingMethodIsValid(string $value)
    {
        $namingMethods = [self::SLUG_NAMING_METHOD, self::HASH_FILE_NAMING_METHOD, self::TIME_NAMING_METHOD];

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
    public static function imageProcessingLibraryIsValid(string $value)
    {
        $imageLibrary = [self::GD_IMAGE_LIBRARY, self::IMAGICK_IMAGE_LIBRARY, self::GMAGICK_IMAGE_LIBRARY];

        if (!in_array($value, $imageLibrary)) {
            throw new Exception("Image processing library [$value] is not valid. valid librarys: [" . implode(', ', $imageLibrary) . "]");
        }
    }

    /**
     * Validate style
     *
     * @param string $name
     * @param string $type
     * @param string $mode
     * @param int $width
     * @param int $height
     * @throws Exception
     */
    public static function styleIsValid(string $name, string $type, string $mode, int $width, int $height)
    {
        self::modeIsValid($mode);

        // validate name
        if (is_numeric($name)) {
            throw new Exception("Style name [$name] is numeric. please use string name for your style");
        }

        // validate width and height
        if ($mode == self::CROP_STYLE_MODE) {
            if (!$height) {
                throw new Exception('Height is required when you are in crop mode');
            }

            if (!$width) {
                throw new Exception('Width is required when you are in crop mode');
            }
        }

        // validate type
        if ($type) {
            $types = [self::IMAGE_STYLE_TYPE, self::VIDEO_STYLE_TYPE];

            if (!in_array($type, $types)) {
                throw new Exception("Style type [$type] is not valid. valid types: [" . implode(', ', $types) . "]");
            }
        }
    }

    /**
     * Validate mode
     *
     * @param string $mode
     * @throws Exception
     */
    public static function modeIsValid(string $mode)
    {
        if ($mode) {
            $modes = [
                self::LANDSCAPE_STYLE_MODE, self::PORTRAIT_STYLE_MODE, self::CROP_STYLE_MODE,
                self::EXACT_STYLE_MODE, self::AUTO_STYLE_MODE
            ];

            if (!in_array($mode, $modes)) {
                throw new Exception("Style mode [$mode] is not valid. valid modes: [" . implode(', ', $modes) . "]");
            }
        }
    }
}
