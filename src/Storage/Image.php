<?php

namespace Mostafaznv\Larupload\Storage;

use ColorThief\ColorThief;
use Exception;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Interfaces\ImageInterface;
use Intervention\Image\ImageManager;
use JetBrains\PhpStorm\ArrayShape;
use Mostafaznv\Larupload\DTOs\Style\ImageStyle;
use Mostafaznv\Larupload\Enums\LaruploadImageLibrary;
use Mostafaznv\Larupload\Enums\LaruploadMediaStyle;
use Symfony\Component\HttpFoundation\File\File;
use Illuminate\Support\Facades\Storage;


class Image
{
    protected readonly UploadedFile $file;

    protected readonly ImageInterface $image;

    protected readonly string $disk;

    protected readonly bool $driverIsLocal;

    protected readonly int $dominantColorQuality;


    public function __construct(UploadedFile $file, string $disk, LaruploadImageLibrary $library, int $dominantColorQuality = 10)
    {
        $this->file = $file;
        $this->disk = $disk;
        $this->driverIsLocal = disk_driver_is_local($this->disk);
        $this->dominantColorQuality = $dominantColorQuality;

        $path = $file->getRealPath();

        $imageManager = $library === LaruploadImageLibrary::GD
            ? ImageManager::gd()
            : ImageManager::imagick();


        $this->image = $imageManager->read($path);
    }


    #[ArrayShape(['width' => 'int', 'height' => 'int'])]
    public function getMeta(): array
    {
        return [
            'width'  => $this->image->width(),
            'height' => $this->image->height(),
        ];
    }

    public function resize(string $saveTo, ImageStyle $style): bool
    {
        $saveTo = Storage::disk($this->disk)->path($saveTo);

        if ($style->mode === LaruploadMediaStyle::SCALE_HEIGHT and $style->width) {
            $this->resizeLandscape($style->width);
        }
        else if ($style->mode == LaruploadMediaStyle::SCALE_WIDTH and $style->height) {
            $this->resizePortrait($style->height);
        }
        else if ($style->mode == LaruploadMediaStyle::CROP and $style->height and $style->width) {
            $this->resizeCrop($style->width, $style->height);
        }
        else if ($style->mode == LaruploadMediaStyle::FIT and $style->height and $style->width) {
            $this->resizeExact($style->width, $style->height);
        }
        else {
            $this->resizeAuto($style->width, $style->height);
        }

        $isSvg = $this->file->getExtension() === 'svg';

        if ($this->driverIsLocal) {
            $isSvg
                ? $this->image->toPng()->save($saveTo)
                : $this->image->encode()->save($saveTo);
        }
        else {
            list($path, $name) = split_larupload_path($saveTo);

            $tempDir = larupload_temp_dir();
            $tempName = time() . '-' . $name;
            $temp = "$tempDir/$tempName";

            $isSvg
                ? $this->image->toPng()->save($temp)
                : $this->image->encode()->save($temp);

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
                $color = ColorThief::getColor(
                    sourceImage: $path,
                    quality: $this->dominantColorQuality,
                    outputFormat: 'hex'
                );

                if ($color) {
                    return $color;
                }
            }
        }
        // @codeCoverageIgnoreStart
        catch (Exception) {
            // do nothing
        }
        // @codeCoverageIgnoreEnd

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
        $this->image->crop(
            width: $width,
            height:  $height,
            position: 'center'
        );
    }

    /**
     * Landscape (width fixed)
     * width given, height automatically selected to preserve aspect ratio
     */
    private function resizeLandscape(int $width): void
    {
        $this->image->scale(
            width: $width
        );
    }

    /**
     * Portrait (height fixed)
     * height given, width automatically selected to preserve aspect ratio
     */
    private function resizePortrait(int $height): void
    {
        $this->image->scale(
            height: $height
        );
    }

    /**
     * Resize an image to an exact width and height.
     * does not preserve aspect ratio.
     */
    private function resizeExact(int $width, int $height): void
    {
        $this->image->resize($width, $height);
    }
}
