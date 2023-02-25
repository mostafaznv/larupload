<?php

namespace Mostafaznv\Larupload\Storage;

use ColorThief\ColorThief;
use Exception;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Image as InterventionImage;
use Intervention\Image\ImageManager;
use JetBrains\PhpStorm\ArrayShape;
use Mostafaznv\Larupload\DTOs\Style;
use Mostafaznv\Larupload\Enums\LaruploadImageLibrary;
use Mostafaznv\Larupload\Enums\LaruploadStyleMode;
use Mostafaznv\Larupload\Helpers\LaraTools;
use Symfony\Component\HttpFoundation\File\File;
use Illuminate\Support\Facades\Storage;


class Image
{
    use LaraTools;

    protected UploadedFile $file;

    protected InterventionImage $image;

    protected string $disk;

    protected string $localDisk;

    protected bool $driverIsLocal;


    public function __construct(UploadedFile $file, string $disk, string $localDisk, LaruploadImageLibrary $library)
    {
        $this->file = $file;
        $this->disk = $disk;
        $this->localDisk = $localDisk;
        $this->driverIsLocal = disk_driver_is_local($this->disk);

        $path = $file->getRealPath();

        $imageManager = new ImageManager([
            'driver' => $library === LaruploadImageLibrary::GD ? 'gd' : 'imagick',
        ]);

        $this->image = $imageManager->make($path);
    }


    #[ArrayShape(['width' => 'int', 'height' => 'int'])]
    public function getMeta(): array
    {
        return [
            'width'  => $this->image->width(),
            'height' => $this->image->height(),
        ];
    }

    public function resize(string $saveTo, Style $style): bool
    {
        $saveTo = Storage::disk($this->disk)->path($saveTo);

        if ($style->mode) {
            if ($style->mode === LaruploadStyleMode::LANDSCAPE and $style->width) {
                $this->resizeLandscape($style->width);
            }
            else if ($style->mode == LaruploadStyleMode::PORTRAIT and $style->height) {
                $this->resizePortrait($style->height);
            }
            else if ($style->mode == LaruploadStyleMode::CROP and $style->height and $style->width) {
                $this->resizeCrop($style->width, $style->height);
            }
            else if ($style->mode == LaruploadStyleMode::EXACT and $style->height and $style->width) {
                $this->resizeExact($style->width, $style->height);
            }
            else {
                $this->resizeAuto($style->width, $style->height);
            }
        }
        else {
            $this->resizeAuto($style->width, $style->height);
        }


        if ($this->driverIsLocal) {
            $this->image->save($saveTo);
        }
        else {
            list($path, $name) = $this->splitPath($saveTo);

            $tempDir = $this->tempDir();
            $tempName = time() . '-' . $name;
            $temp = "$tempDir/$tempName";

            $this->image->save($temp);

            Storage::disk($this->disk)->putFileAs($path, new File($temp), $name);

            @unlink($temp);
        }

        return true;
    }

    /**
     * Retrieve dominant color from image file.
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
                    return $this->rgbToHex($color);
                }
            }
        }
        catch (Exception) {
            // do nothing
        }

        return null;
    }


    /**
     * Resize an image as closely as possible to a given
     * width and height while still maintaining aspect ratio.
     *
     * This method is really just a proxy to other resize methods:
     * — If the current image is wider, we'll resize landscape.
     * — If the current image is taller, we'll resize portrait.
     * — If the image is as tall as it is wide (it's a square) then we'll
     *   apply the same process using the new dimensions (we'll resize exact if
     *   the new dimensions are both equal since at this point we'll have a square
     *   image being resized to a square).
     */
    private function resizeAuto(int $width = null, int $height = null): void
    {
        $originalWidth = $this->image->width();
        $originalHeight = $this->image->height();

        if ($width === null) {
            $width = $originalWidth;
        }

        if ($height === null) {
            $height = $originalHeight;
        }

        if ($originalHeight < $originalWidth) {
            $this->resizeLandscape($width);
            return;
        }

        if ($originalHeight > $originalWidth) {
            $this->resizePortrait($height);
            return;
        }

        if ($height < $width) {
            $this->resizeLandscape($width);
            return;
        }

        if ($height > $width) {
            $this->resizePortrait($height);
            return;
        }

        $this->resizeExact($width, $height);
    }

    /**
     * Resize an image and then center crop it
     */
    private function resizeCrop(int $width, int $height): void
    {
        $this->image->fit($width, $height, function($constraint) {
            $constraint->upsize();
        });
    }

    /**
     * Landscape (width fixed)
     * width given, height automatically selected to preserve aspect ratio
     */
    private function resizeLandscape(int $width): void
    {
        $this->image->widen($width, function($constraint) {
            $constraint->upsize();
        });
    }

    /**
     * Portrait (height fixed)
     * height given, width automatically selected to preserve aspect ratio
     */
    private function resizePortrait(int $height): void
    {
        $this->image->heighten($height, function($constraint) {
            $constraint->upsize();
        });
    }

    /**
     * Resize an image to an exact width and height.
     * does not preserve aspect ratio.
     */
    private function resizeExact(int $width, int $height): void
    {
        $this->image->resize($width, $height);
    }

    /**
     * Convert rgb to hex string (#001100).
     */
    private function rgbToHex($rgb, string $prefix = '#'): string
    {
        return $prefix . str_pad(dechex($this->toInt($rgb)), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Convert rgb array to rgb string
     */
    private function toRgbString($rgb, string $prefix = 'rgb'): ?string
    {
        if (is_array($rgb) and isset($rgb[0]) and isset($rgb[1]) and isset($rgb[2])) {
            return "$prefix($rgb[0],$rgb[1],$rgb[2])";
        }

        return null;
    }

    /**
     * Convert rgb array to int
     */
    private function toInt($rgb): ?int
    {
        if (is_array($rgb) and isset($rgb[0]) and isset($rgb[1]) and isset($rgb[2])) {
            return ($rgb[0] << 16) | ($rgb[1] << 8) | $rgb[2];
        }

        return null;
    }
}
