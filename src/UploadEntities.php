<?php

namespace Mostafaznv\Larupload;

use FFMpeg\Format\Audio\Aac;
use FFMpeg\Format\Audio\Flac;
use FFMpeg\Format\Audio\Mp3;
use FFMpeg\Format\Audio\Wav;
use FFMpeg\Format\Video\Ogg;
use FFMpeg\Format\Video\WebM;
use FFMpeg\Format\Video\X264;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Mostafaznv\Larupload\Actions\GenerateFileIdAction;
use Mostafaznv\Larupload\DTOs\Style\AudioStyle;
use Mostafaznv\Larupload\DTOs\Style\ImageStyle;
use Mostafaznv\Larupload\DTOs\Style\StreamStyle;
use Mostafaznv\Larupload\DTOs\Style\Style;
use Mostafaznv\Larupload\DTOs\Style\VideoStyle;
use Mostafaznv\Larupload\Enums\LaruploadFileType;
use Mostafaznv\Larupload\Enums\LaruploadImageLibrary;
use Mostafaznv\Larupload\Enums\LaruploadMediaStyle;
use Mostafaznv\Larupload\Enums\LaruploadMode;
use Mostafaznv\Larupload\Enums\LaruploadNamingMethod;
use Mostafaznv\Larupload\Enums\LaruploadSecureIdsMethod;
use Mostafaznv\Larupload\Storage\FFMpeg\FFMpeg;
use Mostafaznv\Larupload\Storage\Image;


/**
 * @property ImageStyle[] $imageStyles
 * @property AudioStyle[] $audioStyles
 * @property VideoStyle[] $videoStyles
 * @property StreamStyle[] $streamStyles
 */
class UploadEntities
{
    public UploadedFile|false      $file;
    public UploadedFile|null|false $cover;

    public LaruploadMode      $mode;
    public ?LaruploadFileType $type;

    public string                $name;
    public string                $nameKebab;
    public LaruploadNamingMethod $namingMethod;
    public ?string               $lang;

    public string $folder = '';
    public string $disk;
    public string $localDisk;

    public LaruploadSecureIdsMethod $secureIdsMethod;

    public bool $withMeta;
    public bool $camelCaseResponse;

    public Image                 $image;
    public LaruploadImageLibrary $imageProcessingLibrary;

    public bool       $optimizeImage;
    public bool       $generateCover;
    public ImageStyle $coverStyle;

    public bool $dominantColor;
    public int  $dominantColorQuality = 10;

    public bool $keepOldFiles;
    public bool $preserveFiles;

    public bool $storeOriginalFileName;

    public array $imageStyles = [];
    public array $videoStyles = [];
    public array $audioStyles = [];
    public array $streams     = [];

    public FFMpeg $ffmpeg;
    public bool   $ffmpegQueue;
    public int    $ffmpegMaxQueueNum;

    public bool $uploaded = false;

    /**
     * Model ID / Secure ID
     * This property will be initiated only on retrieving the model.
     */
    public string $id;

    /**
     * Output array to save in the database
     *
     * @var array
     */
    public array $output = [
        'id'             => null,
        'name'           => null,
        'original_name'  => null,
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


    public function __construct(string $name, LaruploadMode $mode)
    {
        $config = config('larupload');

        $this->name = $name;
        $this->nameKebab = str_replace('_', '-', Str::kebab($name));
        $this->mode = $mode;
        $this->disk = $config['disk'];
        $this->localDisk = $config['local-disk'];
        $this->secureIdsMethod = $config['secure-ids'];
        $this->withMeta = $config['with-meta'];
        $this->camelCaseResponse = $config['camel-case-response'];
        $this->namingMethod = $config['naming-method'];
        $this->lang = $config['lang'];
        $this->imageProcessingLibrary = $config['image-processing-library'];
        $this->generateCover = $config['generate-cover'];
        $this->coverStyle = $config['cover-style'];
        $this->dominantColor = $config['dominant-color'];
        $this->dominantColorQuality = $config['dominant-color-quality'];
        $this->keepOldFiles = $config['keep-old-files'];
        $this->preserveFiles = $config['preserve-files'];
        // todo - remove it and store original file name by default in the next major version
        $this->storeOriginalFileName = $config['store-original-file-name'] ?? false;
        $this->optimizeImage = $config['optimize-image']['enable'] ?? false;
        $this->ffmpegQueue = $config['ffmpeg']['queue'];
        $this->ffmpegMaxQueueNum = $config['ffmpeg']['max-queue-num'];
    }

    public static function make(string $name, LaruploadMode $mode = LaruploadMode::HEAVY): self
    {
        return new static($name, $mode);
    }


    # property setters
    public function namingMethod(LaruploadNamingMethod $method): self
    {
        $this->namingMethod = $method;

        return $this;
    }

    public function lang(string $lang): self
    {
        $this->lang = $lang;

        return $this;
    }

    public function folder(string $name): self
    {
        if (!$this->folder) {
            $this->folder = str_replace('_', '-', Str::kebab($name));
        }

        return $this;
    }

    public function disk(string $disk): self
    {
        $this->disk = $disk;

        return $this;
    }

    public function secureIdsMethod(LaruploadSecureIdsMethod $method): self
    {
        $this->secureIdsMethod = $method;

        return $this;
    }

    public function withMeta(bool $status): self
    {
        $this->withMeta = $status;

        return $this;
    }

    public function imageProcessingLibrary(LaruploadImageLibrary $library): self
    {
        $this->imageProcessingLibrary = $library;

        return $this;
    }

    public function optimizeImage(bool $status): self
    {
        $this->optimizeImage = $status;

        return $this;
    }

    public function generateCover(bool $status): self
    {
        $this->generateCover = $status;

        return $this;
    }

    public function coverStyle(string $name, ?int $width = null, ?int $height = null, LaruploadMediaStyle $mode = LaruploadMediaStyle::AUTO): self
    {
        $this->coverStyle = ImageStyle::make($name, $width, $height, $mode);

        return $this;
    }

    public function dominantColor(bool $status): self
    {
        $this->dominantColor = $status;

        return $this;
    }

    public function dominantColorQuality(int $quality): self
    {
        $this->dominantColorQuality = $quality;

        return $this;
    }

    public function keepOldFiles(bool $status): self
    {
        $this->keepOldFiles = $status;

        return $this;
    }

    public function preserveFiles(bool $status): self
    {
        $this->preserveFiles = $status;

        return $this;
    }

    public function storeOriginalFileName(bool $status): self
    {
        $this->storeOriginalFileName = $status;

        return $this;
    }

    public function image(string $name, ?int $width = null, ?int $height = null, LaruploadMediaStyle $mode = LaruploadMediaStyle::AUTO): self
    {
        $this->imageStyles[$name] = ImageStyle::make($name, $width, $height, $mode);

        return $this;
    }

    public function video(string $name, ?int $width = null, ?int $height = null, LaruploadMediaStyle $mode = LaruploadMediaStyle::SCALE_HEIGHT, X264|WebM|Ogg|Mp3|Aac|Wav|Flac $format = new X264, bool $padding = false): self
    {
        $this->videoStyles[$name] = VideoStyle::make($name, $width, $height, $mode, $format, $padding);

        return $this;
    }

    public function audio(string $name, Mp3|Aac|Wav|Flac $format = new Mp3): self
    {
        $this->audioStyles[$name] = AudioStyle::make($name, $format);

        return $this;
    }

    public function stream(string $name, int $width, int $height, X264 $format, LaruploadMediaStyle $mode = LaruploadMediaStyle::SCALE_HEIGHT, bool $padding = false): self
    {
        $this->streams[$name] = StreamStyle::make($name, $width, $height, $format, $mode, $padding);

        return $this;
    }


    # methods
    public function isUploaded(): bool
    {
        return $this->uploaded;
    }

    public function getName(bool $withNameStyle = false): string
    {
        return $withNameStyle ? $this->nameStyle($this->name) : $this->name;
    }

    public function setOutput(Model $model): void
    {
        $this->id = GenerateFileIdAction::make($model, $this->secureIdsMethod, $this->mode, $this->name)->run();

        if ($this->mode === LaruploadMode::HEAVY) {
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

    protected function nameStyle($name): string
    {
        return $this->camelCaseResponse ? Str::camel($name) : $name;
    }

    protected function outputToObject(): object
    {
        $output = (object)$this->output;

        if ($this->camelCaseResponse) {
            $output->mimeType = $output->mime_type;
            $output->dominantColor = $output->dominant_color;
            $output->originalName = $output->original_name;

            unset($output->mime_type);
            unset($output->dominant_color);
            unset($output->original_name);
        }

        return $output;
    }

    protected function getStyle(string $style): ?Style
    {
        $type = $this->output['type'];
        $types = [
            LaruploadFileType::VIDEO->name,
            LaruploadFileType::AUDIO->name,
            LaruploadFileType::IMAGE->name
        ];

        if (in_array($type, $types)) {
            $styles = match ($type) {
                LaruploadFileType::VIDEO->name => $this->videoStyles,
                LaruploadFileType::AUDIO->name => $this->audioStyles,
                LaruploadFileType::IMAGE->name => $this->imageStyles,
            };

            if (isset($styles[$style])) {
                return $styles[$style];
            }
        }

        return null;
    }

    protected function styleHasFile(string $style): bool
    {
        if (in_array($style, [Larupload::ORIGINAL_FOLDER, Larupload::COVER_FOLDER])) {
            return true;
        }

        return $this->getStyle($style) !== null;
    }
}
