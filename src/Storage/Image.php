<?php

namespace Mostafaznv\Larupload\Storage;

use ColorThief\ColorThief;
use Exception;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\File\File;
use Illuminate\Support\Facades\Storage;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Imagine\Image\Point;
use Mostafaznv\Larupload\Helpers\Helper;

/**
 * Class Image
 *
 * Thanks to Travis Bennett for his nice package, https://github.com/CodeSleeve/stapler
 * Some functions used from stapler resize class.
 *
 * @package Mostafaznv\Larupload\Storage
 */
class Image
{
    /**
     * Attached file.
     *
     * @var object
     */
    protected $file;

    /**
     * Larupload configurations.
     *
     * @var array
     */
    protected $config;

    /**
     * Imagine object.
     *
     * @var object
     */
    protected $image;

    /**
     * Image constructor.
     *
     * @param $file
     */
    public function __construct($file)
    {
        $this->config = config('larupload');
        $this->file = $file;

        $path = $file->getRealPath();
        $library = $this->config['image_processing_library'];
        $imagine = new $library();

        $this->image = $imagine->open($path);
    }

    /**
     * Get Metadata from image file.
     *
     * @return array
     */
    public function getMeta()
    {
        $size = $this->image->getSize();

        $meta = [
            'width'  => $size->getWidth(),
            'height' => $size->getHeight(),
        ];

        return $meta;
    }

    /**
     * Resize an image using the computed settings.
     *
     * @param $storage
     * @param $saveTo
     * @param $style
     *
     * @return string
     */
    public function resize($storage, $saveTo, $style)
    {
        list($width, $height, $option) = $this->parseStyleDimensions($style);
        $method = 'resize' . ucfirst($option);

        $image = $this->image;

        $driver = Helper::diskToDriver($storage);

        if ($driver == 'local') {
            $this->$method($image, $width, $height)->save($saveTo);
        }
        else {
            list($path, $name) = Helper::splitPath($saveTo);

            $tempDir = Helper::tempDir();
            $tempName = time() . '-' . $name;
            $temp = $tempDir . "/" . $tempName;

            $this->$method($image, $width, $height)->save($temp);

            $file = new File($temp);

            Storage::disk($storage)->putFileAs($path, $file, $name);
            @unlink($temp);
        }

        return true;
    }

    /**
     * Resize an image as closely as possible to a given
     * width and height while still maintaining aspect ratio.
     * This method is really just a proxy to other resize methods:.
     *
     * If the current image is wider than it is tall, we'll resize landscape.
     * If the current image is taller than it is wide, we'll resize portrait.
     * If the image is as tall as it is wide (it's a squarey) then we'll
     * apply the same process using the new dimensions (we'll resize exact if
     * the new dimensions are both equal since at this point we'll have a square
     * image being resized to a square).
     *
     * @param ImageInterface $image
     * @param string $width - The image's new width.
     * @param string $height - The image's new height.
     *
     * @return ImageInterface
     */
    protected function resizeAuto(ImageInterface $image, $width, $height)
    {
        $size = $this->image->getSize();

        $originalWidth = $size->getWidth();
        $originalHeight = $size->getHeight();

        if (!$width)
            $width = $originalWidth;

        if (!$height)
            $height = $originalHeight;

        if ($originalHeight < $originalWidth) {
            return $this->resizeLandscape($image, $width, $height);
        }

        if ($originalHeight > $originalWidth) {
            return $this->resizePortrait($image, $width, $height);
        }

        if ($height < $width) {
            return $this->resizeLandscape($image, $width, $height);
        }

        if ($height > $width) {
            return $this->resizePortrait($image, $width, $height);
        }

        return $this->resizeExact($image, $width, $height);
    }

    /**
     * Resize an image and then center crop it.
     *
     * @param ImageInterface $image
     * @param string $width - The image's new width.
     * @param string $height - The image's new height.
     *
     * @return ImageInterface
     */
    protected function resizeCrop(ImageInterface $image, $width, $height)
    {
        list($optimalWidth, $optimalHeight) = $this->getOptimalCrop($image->getSize(), $width, $height);

        // Find center - this will be used for the crop
        $centerX = ($optimalWidth / 2) - ($width / 2);
        $centerY = ($optimalHeight / 2) - ($height / 2);
        return $image->resize(new Box($optimalWidth, $optimalHeight))->crop(new Point($centerX, $centerY), new Box($width, $height));
    }

    /**
     * Resize an image as a landscape (width fixed).
     *
     * @param ImageInterface $image
     * @param string $width - The image's new width.
     * @param string $height - The image's new height.
     *
     * @return ImageInterface
     */
    protected function resizeLandscape(ImageInterface $image, $width, $height)
    {
        $optimalHeight = $this->getSizeByFixedWidth($image, $width);
        $dimensions = $image->getSize()->widen($width)->heighten($optimalHeight);
        $image = $image->resize($dimensions);

        return $image;
    }

    /**
     * Resize an image as a portrait (height fixed).
     *
     * @param ImageInterface $image
     * @param string $width - The image's new width.
     * @param string $height - The image's new height.
     *
     * @return ImageInterface
     */
    protected function resizePortrait(ImageInterface $image, $width, $height)
    {
        $optimalWidth = $this->getSizeByFixedHeight($image, $height);
        $dimensions = $image->getSize()->heighten($height)->widen($optimalWidth);
        $image = $image->resize($dimensions);
        return $image;
    }

    /**
     * Returns the height based on the new image width.
     *
     * @param ImageInterface $image
     * @param int $newWidth - The image's new width.
     *
     * @return int
     */
    protected function getSizeByFixedWidth(ImageInterface $image, $newWidth)
    {
        $box = $image->getSize();
        $ratio = $box->getHeight() / $box->getWidth();
        $newHeight = $newWidth * $ratio;

        return $newHeight;
    }

    /**
     * Returns the width based on the new image height.
     *
     * @param ImageInterface $image
     * @param int $newHeight - The image's new height.
     *
     * @return int
     */
    protected function getSizeByFixedHeight(ImageInterface $image, $newHeight)
    {
        $box = $image->getSize();
        $ratio = $box->getWidth() / $box->getHeight();
        $newWidth = $newHeight * $ratio;

        return $newWidth;
    }

    /**
     * Resize an image to an exact width and height.
     *
     * @param ImageInterface $image
     * @param string $width - The image's new width.
     * @param string $height - The image's new height.
     *
     * @return ImageInterface
     */
    protected function resizeExact(ImageInterface $image, $width, $height)
    {
        return $image->resize(new Box($width, $height));
    }

    /**
     * Attempts to find the best way to crop.
     * Takes into account the image being a portrait or landscape.
     *
     * @param Box $size - The image's current size.
     * @param string $width - The image's new width.
     * @param string $height - The image's new height.
     *
     * @return array
     */
    protected function getOptimalCrop(Box $size, $width, $height)
    {
        $heightRatio = $size->getHeight() / $height;
        $widthRatio = $size->getWidth() / $width;

        if ($heightRatio < $widthRatio) {
            $optimalRatio = $heightRatio;
        }
        else {
            $optimalRatio = $widthRatio;
        }

        $optimalHeight = round($size->getHeight() / $optimalRatio, 2);
        $optimalWidth = round($size->getWidth() / $optimalRatio, 2);

        return [$optimalWidth, $optimalHeight];
    }

    /**
     * parseStyleDimensions method.
     *
     * Parse the given style dimensions to extract out the file processing options,
     * perform any necessary image resizing for a given style.
     *
     * @param StyleInterface $style
     *
     * @return array
     */
    protected function parseStyleDimensions($style)
    {
        $width = isset($style['width']) ? $style['width'] : null;
        $height = isset($style['height']) ? $style['height'] : null;
        $mode = isset($style['mode']) ? $style['mode'] : null;

        if ($mode) {
            if ($mode == 'landscape' and $width) {
                // Width given, height automatically selected to preserve aspect ratio (landscape).
                return [$width, null, 'landscape'];
            }
            else if ($mode == 'portrait' and $height) {
                // Height given, width automatically selected to preserve aspect ratio (portrait).
                return [null, $height, 'portrait'];
            }
            else if ($mode == 'crop' and $height and $width) {
                // Resize, then crop.
                return [$width, $height, 'crop'];
            }
            else if ($mode == 'exact' and $height and $width) {
                // Resize by exact width/height (does not preserve aspect ratio).
                return [$width, $height, 'exact'];
            }
        }

        // Let the script decide the best way to resize.
        return [$width, $height, 'auto'];
    }

    /**
     * Fetch dominant color from image file.
     *
     * @param $file
     * @return null|string
     */
    public static function dominant($file)
    {
        try {
            $path = null;


            if ($file instanceof UploadedFile)
                $path = $file->getRealPath();
            else if (file_exists($file))
                $path = $file;

            if ($path) {
                $color = ColorThief::getColor($path);
                if ($color) {
                    return self::toHexString($color);
                }
            }
        }
        catch (Exception $e) {
            // do nothing
        }


        return null;
    }

    /**
     * Convert rgb to hex string (#001100).
     *
     * @param $rgb
     * @param string $prefix
     * @return string
     */
    protected static function toHexString($rgb, $prefix = '#')
    {
        return $prefix . str_pad(dechex(self::toInt($rgb)), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Convert rgb array to rgb string (rgb(0,0,0)).
     *
     * @param $rgb
     * @param string $prefix
     * @return null|string
     */
    protected static function toRgbString($rgb, $prefix = 'rgb')
    {
        if (is_array($rgb) and isset($rgb[0]) and isset($rgb[1]) and isset($rgb[2]))
            return "$prefix({$rgb[0]},{$rgb[1]},{$rgb[2]})";

        return null;
    }

    /**
     * Convert rgb array to int.
     * We use output of this function to convert rgb to hex.
     *
     * @param $rgb
     * @return int|null
     */
    protected static function toInt($rgb)
    {
        if (is_array($rgb) and isset($rgb[0]) and isset($rgb[1]) and isset($rgb[2]))
            return ($rgb[0] << 16) | ($rgb[1] << 8) | $rgb[2];
        return null;
    }
}