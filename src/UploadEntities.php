<?php

namespace Mostafaznv\Larupload;

use Exception;
use Illuminate\Support\Str;
use Mostafaznv\Larupload\Helpers\Validator;

class UploadEntities
{
    /**
     * Name of uploadable entity
     *
     * @var string
     */
    public string $name;

    /**
     * Mode of uploadable entity
     * heavy, light
     *
     * @var string
     */
    protected string $mode;

    /**
     * Folder/Model name
     *
     * @var string
     */
    protected string $folder = '';

    /**
     * Storage Disk
     *
     * @var string
     */
    protected string $disk;

    /**
     * Specify that larupload returns meta values on getAttribute or not
     *
     * @var bool
     */
    protected bool $withMeta;

    /**
     * Method that larupload should use to naming uploaded files
     *
     * @var string
     */
    protected string $namingMethod;

    /**
     * Lang of file name
     *
     * @var string|null
     */
    protected string $lang;

    /**
     * Image processing library
     *
     * @var string
     */
    protected string $imageProcessingLibrary;

    /**
     * Styles for image/video files
     *
     * @var array
     */
    protected array $styles = [];

    /**
     * Stream styles
     *
     * @var array
     */
    protected array $streams = [];

    /**
     * Specify that larupload should generates cover for image and videos or not
     *
     * @var bool
     */
    protected bool $generateCover;

    /**
     * Cover style
     *
     * @var array
     */
    protected array $coverStyle = [];

    /**
     * dominant color flag
     *
     * @var boolean
     */
    protected bool $dominantColor;

    /**
     * Keep old files or not
     *
     * @var boolean
     */
    protected bool $keepOldFiles;

    /**
     * Preserve files or not
     *
     * @var boolean
     */
    protected bool $preserveFiles;


    public function __construct(string $name, string $mode)
    {
        $config = config('larupload');

        $this->name = $name;
        $this->mode = $mode;
        $this->disk = $config['disk'];
        $this->withMeta = $config['with-meta'];
        $this->namingMethod = $config['naming-method'];
        $this->lang = $config['lang'];
        $this->imageProcessingLibrary = $config['image-processing-library'];
        $this->generateCover = $config['generate-cover'];
        $this->coverStyle = $config['cover-style'];
        $this->dominantColor = $config['dominant-color'];
        $this->keepOldFiles = $config['keep-old-files'];
        $this->preserveFiles = $config['preserve-files'];
    }

    /**
     * Create a new upload entity.
     *
     * @param string $name
     * @param string $mode
     * @return UploadEntities
     */
    public static function make(string $name, string $mode = LaruploadEnum::HEAVY_MODE): UploadEntities
    {
        return new static($name, $mode);
    }

    /**
     * Set folder name by user
     *
     * @param string $name
     * @return $this
     */
    public function folder(string $name): UploadEntities
    {
        if (!$this->folder) {
            $this->folder = str_replace('_', '-', Str::kebab($name));
        }

        return $this;
    }

    /**
     * Set storage disk
     *
     * @param string $disk
     * @return $this
     */
    public function disk(string $disk): UploadEntities
    {
        $this->disk = $disk;

        return $this;
    }

    /**
     * Set with meta status
     *
     * @param bool $status
     * @return $this
     */
    public function withMeta(bool $status): UploadEntities
    {
        $this->withMeta = $status;

        return $this;
    }

    /**
     * Set naming method
     *
     * @param string $method
     * @return $this
     * @throws Exception
     */
    public function namingMethod(string $method): UploadEntities
    {
        Validator::namingMethodIsValid($method);

        $this->namingMethod = $method;

        return $this;
    }

    /**
     * Set lang
     *
     * @param string $lang
     * @return $this
     */
    public function lang(string $lang): UploadEntities
    {
        $this->lang = $lang;

        return $this;
    }

    /**
     * Set image processing library
     *
     * @param string $library
     * @return $this
     * @throws Exception
     */
    public function imageProcessingLibrary(string $library): UploadEntities
    {
        Validator::imageProcessingLibraryIsValid($library);

        $this->imageProcessingLibrary = $library;

        return $this;
    }

    /**
     * Set style
     *
     * @param string $name
     * @param string|null $type
     * @param string|null $mode
     * @param int|null $width
     * @param int|null $height
     * @return $this
     * @throws Exception
     */
    public function style(string $name, string $type = null, string $mode = null, int $width = null, int $height = null): UploadEntities
    {
        Validator::styleIsValid($name, $type, $mode, $width, $height);

        $this->styles[$name] = [
            'type'   => $type,
            'mode'   => $mode,
            'width'  => $width,
            'height' => $height
        ];

        return $this;
    }

    /**
     * Set stream style
     * @param string $name
     * @param int $width
     * @param int $height
     * @param int $audioBitrate
     * @param int $videoBitrate
     * @return $this
     */
    public function stream(string $name, int $width, int $height, int $audioBitrate, int $videoBitrate): UploadEntities
    {
        $this->streams[$name] = [
            'width'   => $width,
            'height'  => $height,
            'bitrate' => [
                'audio' => $audioBitrate,
                'video' => $videoBitrate
            ]
        ];

        return $this;
    }

    /**
     * Generate cover status
     *
     * @param bool $status
     * @return $this
     */
    public function generateCover(bool $status): UploadEntities
    {
        $this->generateCover = $status;

        return $this;
    }

    /**
     * Set cover style
     *
     * @param int $width
     * @param int $height
     * @param string $mode
     * @return $this
     * @throws Exception
     */
    public function coverStyle(int $width, int $height, string $mode): UploadEntities
    {
        Validator::modeIsValid($mode);

        $this->coverStyle = [
            'width'  => $width,
            'height' => $height,
            'mode'   => $mode
        ];

        return $this;
    }

    /**
     * Set dominant color status
     *
     * @param bool $status
     * @return $this
     */
    public function dominantColor(bool $status): UploadEntities
    {
        $this->dominantColor = $status;

        return $this;
    }
}
