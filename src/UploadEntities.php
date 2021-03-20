<?php

namespace Mostafaznv\Larupload;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Mostafaznv\Larupload\Helpers\Validator;
use Mostafaznv\Larupload\Storage\FFMpeg;
use Mostafaznv\Larupload\Storage\Image;

class UploadEntities
{
    /**
     * File object
     *
     * @var UploadedFile
     */
    protected UploadedFile $file;

    /**
     * Name of uploadable entity
     *
     * @var string
     */
    protected string $name;
    protected string $nameKebab;

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
     * Type to get type of attached file
     *
     * @var string
     */
    protected string $type;

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

    /**
     * Output array to save in database
     *
     * @var array
     */
    protected array $output = [
        'name'           => null,
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

    /**
     * FFMPEG instance
     *
     * @var FFMpeg
     */
    protected FFMpeg $ffmpeg;

    /**
     * Specify if FFMPEG process should run through queue or not
     *
     * @var bool
     */
    protected bool $ffmpegQueue;

    /**
     * Specify max FFMPEG processes should run at the same time
     *
     * @var int
     */
    protected int $ffmpegMaxQueueNum;

    /**
     * FFMPEG capture frame
     *
     * @var mixed
     */
    protected $ffmpegCaptureFrame;

    /**
     * Image instance
     *
     * @var Image
     */
    protected Image $image;

    public function __construct(string $name, string $mode)
    {
        $config = config('larupload');

        $this->name = $name;
        $this->nameKebab = str_replace('_', '-', Str::kebab($name));
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
        $this->ffmpegQueue = $config['ffmpeg']['queue'];
        $this->ffmpegMaxQueueNum = $config['ffmpeg']['max-queue-num'];
        $this->ffmpegCaptureFrame = $config['ffmpeg']['capture-frame'];
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
     * FFMPEG instance
     *
     * @param UploadedFile|null $file
     * @return FFMpeg
     */
    protected function ffmpeg(UploadedFile $file = null): FFMpeg
    {
        if (!isset($this->ffmpeg) or $file) {
            $this->ffmpeg = new FFMpeg($this->file, $this->disk);
        }

        return $this->ffmpeg;
    }

    /**
     * Image instance
     *
     * @param UploadedFile|null $file
     * @return Image
     */
    protected function image(UploadedFile $file = null): Image
    {
        if (!isset($this->image) or $file) {
            $this->image = new Image($file ?? $this->file, $this->disk);
        }

        return $this->image;
    }

    /**
     * Generate file name using naming method
     *
     * @return string
     */
    protected function setFileName(): string
    {
        $format = $this->file->getClientOriginalExtension();

        switch ($this->namingMethod) {
            case 'hash_file':
                $name = hash_file('md5', $this->file->getRealPath());
                break;

            case 'time':
                $name = time();
                break;


            default:
                $name = $this->file->getClientOriginalName();
                $name = pathinfo($name, PATHINFO_FILENAME);
                $num = rand(0, 9999);

                $str = new \Mostafaznv\Larupload\Helpers\Str($this->lang);
                $name = $str->generateSlug($name) . "-" . $num;
                break;
        }

        return "{$name}.$format";
    }

    /**
     * Name Accessor
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
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
     * @param array $type
     * @param string|null $mode
     * @param int|null $width
     * @param int|null $height
     * @return $this
     * @throws Exception
     */
    public function style(string $name, array $type = [], string $mode = null, int $width = null, int $height = null): UploadEntities
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
     *
     * @param string $name
     * @param int $width
     * @param int $height
     * @param string|int $audioBitrate
     * @param string|int $videoBitrate
     * @return $this
     * @throws Exception
     */
    public function stream(string $name, int $width, int $height, $audioBitrate, $videoBitrate): UploadEntities
    {
        Validator::streamIsValid($name, $width, $height, $audioBitrate, $videoBitrate);

        $this->streams[$name] = [
            'width'   => $width,
            'height'  => $height,
            'bitrate' => [
                'audio' => $this->shortNumberToInteger($audioBitrate),
                'video' => $this->shortNumberToInteger($videoBitrate)
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

    /**
     * Convert short number formats to integer
     * Example: 1M -> 1000000
     *
     * @param $number
     * @return int
     */
    protected function shortNumberToInteger($number): int
    {
        $number = strtoupper($number);

        $units = [
            'M' => '1000000',
            'K' => '1000',
        ];

        $unit = substr($number, -1);

        if (!array_key_exists($unit, $units)) {
            return (int)$number;
        }

        $number = (float)$number * $units[$unit];

        return (int)$number;
    }
}