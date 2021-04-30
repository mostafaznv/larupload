<?php

namespace Mostafaznv\Larupload\Storage;

use ColorThief\ColorThief;
use Exception;
use Illuminate\Http\UploadedFile;
use Imagine\Image\BoxInterface;
use Mostafaznv\Larupload\Helpers\LaraTools;
use Mostafaznv\Larupload\LaruploadEnum;
use Symfony\Component\HttpFoundation\File\File;
use Illuminate\Support\Facades\Storage;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Imagine\Image\Point;

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
    use LaraTools;

    /**
     * Attached file
     *
     * @var UploadedFile
     */
    protected UploadedFile $file;

    /**
     * Imagine object
     *
     * @var object
     */
    protected object $image;

    /**
     * Storage Disk
     *
     * @var string
     */
    protected string $disk;

    /**
     * Storage local disk
     *
     * @var string
     */
    protected string $localDisk;

    /**
     * Specify if driver is local or not
     *
     * @var bool
     */
    protected bool $driverIsLocal;

    /**
     * Image constructor
     *
     * @param UploadedFile $file
     * @param string $disk
     * @param string $localDisk
     * @param string $library
     */
    public function __construct(UploadedFile $file, string $disk, string $localDisk, string $library)
    {
        $this->file = $file;
        $this->disk = $disk;
        $this->localDisk = $localDisk;
        $this->driverIsLocal = $this->diskDriverIsLocal($this->disk);

        $path = $file->getRealPath();
        $imagine = new $library();

        $this->image = $imagine->open($path);
    }

    /**
     * Get Metadata from image file
     *
     * @return array
     */
    public function getMeta(): array
    {
        $size = $this->image->getSize();

        return [
            'width'  => (int)$size->getWidth(),
            'height' => (int)$size->getHeight(),
        ];
    }

    /**
     * Resize an image using the computed settings
     *
     * @param string $saveTo
     * @param array $style
     *
     * @return bool
     */
    public function resize(string $saveTo, array $style): bool
    {
        list($width, $height, $option) = $this->parseStyleDimensions($style);

        $method = 'resize' . ucfirst($option);
        $saveTo = Storage::disk($this->disk)->path($saveTo);
        $image = $this->image;

        if ($this->driverIsLocal) {
            $this->$method($image, $width, $height)->save($saveTo);
        }
        else {
            list($path, $name) = $this->splitPath($saveTo);

            $tempDir = $this->tempDir();
            $tempName = time() . '-' . $name;
            $temp = "{$tempDir}/{$tempName}";

            $this->$method($image, $width, $height)->save($temp);

            Storage::disk($this->disk)->putFileAs($path, new File($temp), $name);

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
     * @param int|null $width
     * @param int|null $height
     * @return ImageInterface
     */
    protected function resizeAuto(ImageInterface $image, int $width = null, int $height = null): ImageInterface
    {
        $size = $this->image->getSize();
        $originalWidth = $size->getWidth();
        $originalHeight = $size->getHeight();

        if ($width === null) {
            $width = $originalWidth;
        }

        if ($height === null) {
            $height = $originalHeight;
        }

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
     * Resize an image and then center crop it
     *
     * @param ImageInterface $image
     * @param int $width - The image's new width
     * @param int $height - The image's new height
     *
     * @return ImageInterface
     */
    protected function resizeCrop(ImageInterface $image, int $width, int $height): ImageInterface
    {
        list($optimalWidth, $optimalHeight) = $this->getOptimalCrop($image->getSize(), $width, $height);

        // find center - this will be used for the crop
        $centerX = ($optimalWidth / 2) - ($width / 2);
        $centerY = ($optimalHeight / 2) - ($height / 2);

        return $image->resize(new Box($optimalWidth, $optimalHeight))->crop(new Point($centerX, $centerY), new Box($width, $height));
    }

    /**
     * Resize an image as a landscape (width fixed)
     *
     * @param ImageInterface $image
     * @param int $width - The image's new width.
     * @param int|null $height - The image's new height.
     *
     * @return ImageInterface
     */
    protected function resizeLandscape(ImageInterface $image, int $width, int $height = null): ImageInterface
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
     * @param int|null $width - The image's new width.
     * @param int $height - The image's new height.
     *
     * @return ImageInterface
     */
    protected function resizePortrait(ImageInterface $image, int $width = null, int $height): ImageInterface
    {
        $optimalWidth = $this->getSizeByFixedHeight($image, $height);
        $dimensions = $image->getSize()->heighten($height)->widen($optimalWidth);

        return $image->resize($dimensions);
    }

    /**
     * Returns the height based on the new image width.
     *
     * @param ImageInterface $image
     * @param int $newWidth - The image's new width.
     *
     * @return int
     */
    protected function getSizeByFixedWidth(ImageInterface $image, int $newWidth): int
    {
        $box = $image->getSize();
        $ratio = $box->getHeight() / $box->getWidth();

        return $newWidth * $ratio;
    }

    /**
     * Returns the width based on the new image height.
     *
     * @param ImageInterface $image
     * @param int $newHeight - The image's new height.
     *
     * @return int
     */
    protected function getSizeByFixedHeight(ImageInterface $image, int $newHeight): int
    {
        $box = $image->getSize();
        $ratio = $box->getWidth() / $box->getHeight();

        return $newHeight * $ratio;
    }

    /**
     * Resize an image to an exact width and height.
     *
     * @param ImageInterface $image
     * @param int $width - The image's new width.
     * @param int $height - The image's new height.
     *
     * @return ImageInterface
     */
    protected function resizeExact(ImageInterface $image, int $width, int $height): ImageInterface
    {
        return $image->resize(new Box($width, $height));
    }

    /**
     * Attempts to find the best way to crop.
     * Takes into account the image being a portrait or landscape.
     *
     * @param BoxInterface $size - The image's current size.
     * @param int $width - The image's new width.
     * @param int $height - The image's new height.
     * @return array
     */
    protected function getOptimalCrop(BoxInterface $size, int $width, int $height): array
    {
        $heightRatio = $size->getHeight() / $height;
        $widthRatio = $size->getWidth() / $width;
        $optimalRatio = ($heightRatio < $widthRatio) ? $heightRatio : $widthRatio;

        $optimalHeight = round($size->getHeight() / $optimalRatio, 2);
        $optimalWidth = round($size->getWidth() / $optimalRatio, 2);

        return [$optimalWidth, $optimalHeight];
    }

    /**
     * Parse Style Dimensions
     *
     * Parse the given style dimensions to extract out the file processing options,
     * perform any necessary image resizing for a given style.
     *
     * @param array $style
     * @return array
     */
    protected function parseStyleDimensions(array $style): array
    {
        $width = isset($style['width']) ? $style['width'] : null;
        $height = isset($style['height']) ? $style['height'] : null;
        $mode = isset($style['mode']) ? $style['mode'] : null;

        if ($mode) {
            // width given, height automatically selected to preserve aspect ratio (landscape).
            if ($mode == LaruploadEnum::LANDSCAPE_STYLE_MODE and $width) {
                return [$width, null, 'landscape'];
            }
            // height given, width automatically selected to preserve aspect ratio (portrait).
            else if ($mode == LaruploadEnum::PORTRAIT_STYLE_MODE and $height) {
                return [null, $height, 'portrait'];
            }
            // resize, then crop.
            else if ($mode == LaruploadEnum::CROP_STYLE_MODE and $height and $width) {
                return [$width, $height, 'crop'];
            }
            // resize by exact width/height (does not preserve aspect ratio).
            else if ($mode == LaruploadEnum::EXACT_STYLE_MODE and $height and $width) {
                return [$width, $height, 'exact'];
            }
        }

        // let the script decide the best way to resize.
        return [$width, $height, 'auto'];
    }

    /**
     * Retrieve dominant color from image file.
     *
     * @param $file
     * @return null|string
     */
    public function getDominantColor($file = null): ?string
    {
        if (is_null($file)) {
            $file = $this->file;
        }

        try {
            $path = null;

            if ($file instanceof UploadedFile) {
                $path = $file->getRealPath();
            }
            else if (file_exists($file)) {
                $path = $file;
            }

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
    protected static function toHexString($rgb, string $prefix = '#'): string
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
    protected static function toRgbString($rgb, string $prefix = 'rgb'): ?string
    {
        if (is_array($rgb) and isset($rgb[0]) and isset($rgb[1]) and isset($rgb[2])) {
            return "$prefix({$rgb[0]},{$rgb[1]},{$rgb[2]})";
        }

        return null;
    }

    /**
     * Convert rgb array to int.
     * We use output of this function to convert rgb to hex.
     *
     * @param $rgb
     * @return int|null
     */
    protected static function toInt($rgb): ?int
    {
        if (is_array($rgb) and isset($rgb[0]) and isset($rgb[1]) and isset($rgb[2])) {
            return ($rgb[0] << 16) | ($rgb[1] << 8) | $rgb[2];
        }

        return null;
    }
}
