<?php

namespace Mostafaznv\Larupload\Storage\FFMpeg;

use Alchemy\BinaryDriver\Exception\ExecutionFailureException;
use FFMpeg\Coordinate\Dimension;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\Exception\RuntimeException;
use FFMpeg\FFMpeg as FFMpegLib;
use FFMpeg\Filters\Video\ResizeFilter;
use FFMpeg\Format\Video\X264;
use FFMpeg\Media\Audio;
use FFMpeg\Media\Video;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Mostafaznv\Larupload\DTOs\FFMpeg\FFMpegMeta;
use Mostafaznv\Larupload\DTOs\Style\ImageStyle;
use Mostafaznv\Larupload\DTOs\Style\StreamStyle;
use Mostafaznv\Larupload\DTOs\Style\VideoStyle;
use Mostafaznv\Larupload\Enums\LaruploadImageLibrary;
use Mostafaznv\Larupload\Storage\Image;
use Psr\Log\LoggerInterface;

class FFMpeg
{
    private readonly UploadedFile $file;

    private readonly string $disk;

    private readonly int $dominantColorQuality;

    private FFMpegMeta $meta;

    private Video|Audio $media;


    /**
     * Default scale size
     * we use this value if width and height both were undefined
     *
     * @var integer
     */
    private const DEFAULT_SCALE = 850;


    public function __construct(UploadedFile $file, string $disk, int $dominantColorQuality)
    {
        $this->file = $file;
        $this->disk = $disk;
        $this->dominantColorQuality = $dominantColorQuality;

        $config = config('larupload.ffmpeg');

        $ffmpeg = FFMpegLib::create([
            'ffmpeg.binaries'  => $config['ffmpeg-binaries'],
            'ffprobe.binaries' => $config['ffprobe-binaries'],
            'timeout'          => $config['timeout'],
            'ffmpeg.threads'   => $config['threads'] ?? 12,
        ], $this->logChannel());

        $this->media = $ffmpeg->open($file->getRealPath());
    }


    public function getMedia(): Video|Audio
    {
        return $this->media;
    }

    public function getMeta(): FFMpegMeta
    {
        if (empty($this->meta)) {
            $meta = $this->media->getStreams()->first()->all();

            // support rotate tag in old ffmpeg versions
            if (isset($meta['tags']['rotate'])) {
                // @codeCoverageIgnoreStart
                $rotate = $meta['tags']['rotate'];

                if ($rotate == 90 or $rotate == 270) {
                    list($meta['height'], $meta['width']) = array($meta['width'], $meta['height']);
                }
                // @codeCoverageIgnoreEnd
            }

            $this->meta = FFMpegMeta::make(
                width: $meta['width'] ?? null,
                height: $meta['height'] ?? null,
                duration: $meta['duration']
            );
        }

        return $this->meta;
    }

    public function setMeta(FFMpegMeta $meta): self
    {
        $this->meta = $meta;

        return $this;
    }

    public function capture(int|float|null $fromSeconds, ImageStyle $style, string $saveTo, bool $withDominantColor = false): ?string
    {
        $dominantColor = null;
        $saveTo = get_larupload_save_path($this->disk, $saveTo);


        $style->mode->ffmpegResizeFilter()
            ? $this->resize($style)
            : $this->crop($style);


        $this->frame($fromSeconds, $saveTo);

        if ($withDominantColor) {
            $dominantColor = $this->dominantColor($saveTo['local']);
        }

        larupload_finalize_save($this->disk, $saveTo);

        return $dominantColor;
    }

    public function manipulate(VideoStyle $style, string $saveTo): void
    {
        $saveTo = get_larupload_save_path($this->disk, $saveTo);

        $style->mode->ffmpegResizeFilter()
            ? $this->resize($style)
            : $this->crop($style);

        $this->media->save($style->format, $saveTo['local']);
        larupload_finalize_save($this->disk, $saveTo);
    }

    public function stream(array $styles, string $basePath, string $fileName): bool
    {
        $hls = new HLS($this, $this->disk);

        return $hls->export($styles, $basePath, $fileName);
    }

    public function resize(VideoStyle|ImageStyle|StreamStyle $style): void
    {
        $dimension = $this->dimension($style);

        if ($style->padding) {
            $this->media->filters()->pad($dimension)->synchronize();
        }
        else {
            $mode = $style->mode->ffmpegResizeFilter() ?? ResizeFilter::RESIZEMODE_SCALE_HEIGHT;

            $this->media->filters()
                ->resize($dimension, $mode)
                ->synchronize();
        }
    }

    public function crop(VideoStyle|ImageStyle|StreamStyle $style): void
    {
        $meta = $this->getMeta();
        $width = $style->width ?? null;
        $height = $style->height ?? null;
        $scale = $width ?: ($height ?: self::DEFAULT_SCALE);
        $scaleType = $meta->width >= $meta->height ? "-1:$scale" : "$scale:-1";

        if ($width and $height) {
            $this->media->filters()->custom("scale=$scaleType,crop=$width:$height,setsar=1");
        }
        else {
            $this->media->filters()->custom("scale=$scaleType,crop=$scale:$scale,setsar=1");
        }
    }

    public function frame(int|float|null $fromSeconds, array $saveTo): void
    {
        if (is_null($fromSeconds)) {
            $fromSeconds = $this->getMeta()->duration / 2;
        }

        $saveToPath = $saveTo['local'];
        $commands = [
            '-y', '-ss', (string)TimeCode::fromSeconds($fromSeconds),
            '-i', $this->media->getPathfile(),
            '-vframes', '1',
            '-f', 'image2',
        ];


        foreach ($this->media->getFiltersCollection() as $filter) {
            $commands = array_merge($commands, $filter->apply($this->media, new X264));
        }

        $commands = array_merge($commands, [$saveToPath]);

        try {
            $this->media->getFFMpegDriver()->command($commands);
        }
        catch (ExecutionFailureException $e) {
            if (file_exists($saveToPath) && is_writable($saveToPath)) {
                unlink($saveToPath);
            }
            throw new RuntimeException('Unable to save frame', $e->getCode(), $e);
        }
    }

    public function clone(bool $withMeta = false): FFMpeg
    {
        $ffmpeg = new self($this->file, $this->disk, $this->dominantColorQuality);

        if ($withMeta) {
            $ffmpeg->setMeta($this->getMeta());
        }

        return $ffmpeg;
    }

    private function logChannel(): ?LoggerInterface
    {
        $channel = config('larupload.ffmpeg.log-channel');

        if ($channel === false) {
            return null;
        }

        return Log::channel($channel ?: config('logging.default'));
    }

    private function dimension(VideoStyle|ImageStyle|StreamStyle $style): Dimension
    {
        $width = $style->width ?: (!$style->height ? self::DEFAULT_SCALE : 1);
        $height = $style->height ?: (!$style->width ? self::DEFAULT_SCALE : 1);

        return new Dimension($width, $height);
    }

    private function dominantColor($path): ?string
    {
        $file = new UploadedFile($path, basename($path));
        $image = new Image($file, $this->disk, LaruploadImageLibrary::GD, $this->dominantColorQuality);

        return $image->getDominantColor();
    }
}
