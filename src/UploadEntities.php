<?php

namespace Mostafaznv\Larupload;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mostafaznv\Larupload\Helpers\LaraTools;
use Mostafaznv\Larupload\Helpers\Slug;
use Mostafaznv\Larupload\Helpers\Validator;
use Mostafaznv\Larupload\Storage\FFMpeg;
use Mostafaznv\Larupload\Storage\Image;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UploadEntities
{
    use LaraTools;

    /**
     * File object
     *
     * @var UploadedFile|mixed
     */
    protected $file;

    /**
     * Cover Object
     *
     * @var UploadedFile|mixed
     */
    protected $cover;

    /**
     * Name of uploadable entity
     *
     * @var string
     */
    protected string $name;
    protected string $nameKebab;

    /**
     * Model ID
     * this property will initiated only on retrieving model.
     *
     * @var string
     */
    protected string $id;

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
     * Storage disk
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
     * Specify that larupload returns meta values on getAttribute or not
     *
     * @var bool
     */
    protected bool $withMeta;

    /**
     * Specify that larupload returns responses camel-cased or not
     *
     * @var bool
     */
    protected bool $camelCaseResponse;

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

    /**
     * Uploaded flag to prevent infinite loop
     *
     * @var bool
     */
    protected bool $uploaded = false;

    public function __construct(string $name, string $mode)
    {
        $config = config('larupload');

        $this->name = $name;
        $this->nameKebab = str_replace('_', '-', Str::kebab($name));
        $this->mode = $mode;
        $this->disk = $config['disk'];
        $this->localDisk = $config['local-disk'];
        $this->withMeta = $config['with-meta'];
        $this->camelCaseResponse = $config['camel-case-response'];
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
            $this->ffmpeg = new FFMpeg($this->file, $this->disk, $this->localDisk);
        }

        return $this->ffmpeg;
    }

    /**
     * Image instance
     *
     * @param UploadedFile $file
     * @return Image
     */
    protected function image(UploadedFile $file): Image
    {
        $this->image = new Image($file ?? $this->file, $this->disk, $this->localDisk);

        return $this->image;
    }

    /**
     * Generate file name using naming method
     *
     * @param UploadedFile|null $file
     * @return string
     */
    protected function setFileName(UploadedFile $file = null): string
    {
        $file = $file ?? $this->file;
        $format = $file->getClientOriginalExtension();

        switch ($this->namingMethod) {
            case 'hash_file':
                $name = hash_file('md5', $file->getRealPath());
                break;

            case 'time':
                $name = time();
                break;


            default:
                $name = $file->getClientOriginalName();
                $name = pathinfo($name, PATHINFO_FILENAME);
                $num = rand(0, 9999);

                $slug = Slug::make($this->lang)->generate($name);
                $name = "{$slug}-{$num}";
                break;
        }

        return "{$name}.$format";
    }

    /**
     * Name Accessor
     *
     * @param bool $withNameStyle
     * @return string
     */
    public function getName(bool $withNameStyle = false): string
    {
        return $withNameStyle ? $this->nameStyle($this->name) : $this->name;
    }

    /**
     * Mode Accessor
     *
     * @return string
     */
    public function getMode(): string
    {
        return $this->mode;
    }

    /**
     * Set model attributes for current entity
     *
     * @param Model $model
     */
    public function setOutput(Model $model)
    {
        $this->id = $model->id;

        if ($this->mode == LaruploadEnum::HEAVY_MODE) {
            foreach ($this->output as $key => $value) {
                $this->output[$key] = $model->{"{$this->name}_file_$key"};
            }
        }
        else {
            $meta = json_decode($model->{"{$this->name}_file_meta"}, true);

            if (is_array($meta)) {
                foreach ($meta as $key => $value) {
                    $this->output[$key] = $value;
                }
            }
        }
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
     * @param int|null $width
     * @param int|null $height
     * @param string|null $mode
     * @param array $type
     * @return $this
     * @throws Exception
     */
    public function style(string $name, int $width = null, int $height = null, string $mode = null, array $type = []): UploadEntities
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
     * @param bool $status
     * @return $this
     */
    public function preserveFiles(bool $status): UploadEntities
    {
        $this->preserveFiles = $status;

        return $this;
    }

    /**
     * Uploaded status
     *
     * @return bool
     */
    public function isUploaded(): bool
    {
        return $this->uploaded;
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

    /**
     * Check if style has file
     *
     * @param $style
     * @return bool
     */
    protected function styleHasFile(string $style): bool
    {
        if (in_array($style, [LaruploadEnum::ORIGINAL_FOLDER, LaruploadEnum::COVER_FOLDER])) {
            return true;
        }

        if (array_key_exists($style, $this->styles)) {
            $type = $this->output['type'];

            if (in_array($type, [LaruploadEnum::VIDEO, LaruploadEnum::IMAGE])) {
                $styleTypes = $this->styles[$style]['type'];

                return count($styleTypes) == 0 or in_array($type, $styleTypes);
            }
        }

        return false;
    }

    /**
     * Fix Exception Names
     *
     * In some special cases we should use other file names instead of the original one
     * Example: when user uploads a svg image, we should change the converted format to jpg! so we have to manipulate file name
     *
     * @param string $name
     * @param string $style
     * @return mixed
     */
    protected function fixExceptionNames(string $name, string $style): string
    {
        if (!in_array($style, [LaruploadEnum::ORIGINAL_FOLDER, LaruploadEnum::COVER_FOLDER])) {
            if (Str::endsWith($name, 'svg')) {
                $name = str_replace('svg', 'jpg', $name);
            }
        }

        return $name;
    }

    /**
     * Convert path to URL, based on storage driver
     *
     * @param string $path
     * @return string|null
     */
    protected function storageUrl(string $path): ?string
    {
        if (isset($this->file) and $this->file == LARUPLOAD_NULL) {
            return null;
        }

        if ($this->driverIsLocal()) {
            $url = Storage::disk($this->disk)->url($path);

            return url($url);
        }

        $baseUrl = config("filesystems.disks.{$this->disk}.url");
        if ($baseUrl) {
            return "$baseUrl/$path";
        }

        return $path;
    }

    /**
     * Download path based on storage driver
     *
     * @param string $path
     * @return RedirectResponse|StreamedResponse|null
     */
    protected function storageDownload(string $path)
    {
        if (isset($this->file) and $this->file == LARUPLOAD_NULL) {
            return null;
        }

        if ($this->driverIsLocal()) {
            return Storage::disk($this->disk)->download($path);
        }

        $baseUrl = config("filesystems.disks.{$this->disk}.url");
        if ($baseUrl) {
            return redirect("$baseUrl/$path");
        }

        return null;
    }

    /**
     * Check if disk is using local driver
     *
     * @return bool
     */
    protected function driverIsLocal(): bool
    {
        return $this->diskDriverIsLocal($this->disk);
    }

    /**
     * Check if disk is not using local driver
     *
     * @return bool
     */
    protected function driverIsNotLocal(): bool
    {
        return !$this->driverIsLocal();
    }

    /**
     * Prepare output array to response
     *
     * @return object
     */
    protected function outputToObject(): object
    {
        $output = (object)$this->output;

        if ($this->camelCaseResponse) {
            $output->mimeType = $output->mime_type;
            $output->dominantColor = $output->dominant_color;

            unset($output->mime_type);
            unset($output->dominant_color);
        }

        return $output;
    }

    /**
     * Name Style
     * check if we should convert name to camel-case style
     *
     * @param $name
     * @return string
     */
    protected function nameStyle($name): string
    {
        return $this->camelCaseResponse ? Str::camel($name) : $name;
    }
}
