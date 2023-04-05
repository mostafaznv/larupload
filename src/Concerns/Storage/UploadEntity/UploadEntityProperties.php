<?php

namespace Mostafaznv\Larupload\Concerns\Storage\UploadEntity;

use Illuminate\Http\UploadedFile;
use Mostafaznv\Larupload\Enums\LaruploadFileType;
use Mostafaznv\Larupload\Enums\LaruploadMode;
use Mostafaznv\Larupload\Enums\LaruploadSecureIdsMethod;
use Mostafaznv\Larupload\UploadEntities;

trait UploadEntityProperties
{
    /**
     * File object
     *
     * @var UploadedFile|mixed
     */
    protected mixed $file;

    /**
     * Cover Object
     *
     * @var UploadedFile|mixed
     */
    protected mixed $cover;

    /**
     * Model ID / Secure ID
     * This property will be initiated only on retrieving model.
     */
    protected string $id;

    /**
     * Type of file in Larupload ecosystem
     *
     * @var LaruploadFileType|null
     */
    protected ?LaruploadFileType $type;

    /**
     * Mode of uploadable entity
     */
    protected LaruploadMode $mode;

    /**
     * Specify whether Larupload should generate a cover image for images and videos or not.
     */
    protected bool $generateCover;

    /**
     * Dominant color flag
     *
     * @var boolean
     */
    protected bool $dominantColor;

    /**
     * Dominant color quality
     *
     * @var int
     */
    protected int $dominantColorQuality = 10;

    /**
     * Specify whether Larupload should Keep old files or not
     */
    protected bool $keepOldFiles;

    /**
     * Specify whether Larupload should preserve files or not
     */
    protected bool $preserveFiles;

    /**
     * Specify whether Larupload should optimize image or not
     */
    protected bool $optimizeImage;

    /**
     * Specify SecurityIDs Method
     */
    protected LaruploadSecureIdsMethod $secureIdsMethod;

    /**
     * Uploaded flag to prevent infinite loop
     */
    protected bool $uploaded = false;

    /**
     * Output array to save in database
     *
     * @var array
     */
    protected array $output = [
        'name'           => null,
        'id'             => null,
        'size'           => null,
        'type'           => null,
        'mime_type'      => null,
        'width'          => null,
        'height'         => null,
        'duration'       => null,
        'dominant_color' => null,
        'format'         => null,
        'cover'          => null,
    ];


    public function generateCover(bool $status): UploadEntities
    {
        $this->generateCover = $status;

        return $this;
    }

    public function dominantColor(bool $status): UploadEntities
    {
        $this->dominantColor = $status;

        return $this;
    }

    public function dominantColorQuality(int $quality): UploadEntities
    {
        $this->dominantColorQuality = $quality;

        return $this;
    }

    public function isUploaded(): bool
    {
        return $this->uploaded;
    }

    public function preserveFiles(bool $status): UploadEntities
    {
        $this->preserveFiles = $status;

        return $this;
    }

    public function optimizeImage(bool $status): UploadEntities
    {
        $this->optimizeImage = $status;

        return $this;
    }

    public function secureIdsMethod(LaruploadSecureIdsMethod $method): UploadEntities
    {
        $this->secureIdsMethod = $method;

        return $this;
    }
}
