<?php

namespace Mostafaznv\Larupload\Concerns\Storage\UploadEntity;

use FFMpeg\Format\Audio\Aac;
use FFMpeg\Format\Audio\Flac;
use FFMpeg\Format\Audio\Mp3;
use FFMpeg\Format\Audio\Wav;
use FFMpeg\Format\Video\X264;
use Mostafaznv\Larupload\Larupload;
use Mostafaznv\Larupload\UploadEntities;
use Mostafaznv\Larupload\DTOs\Style\AudioStyle;
use Mostafaznv\Larupload\DTOs\Style\ImageStyle;
use Mostafaznv\Larupload\DTOs\Style\VideoStyle;
use Mostafaznv\Larupload\DTOs\Style\StreamStyle;
use Mostafaznv\Larupload\Enums\LaruploadFileType;
use Mostafaznv\Larupload\Enums\LaruploadMediaStyle;

trait UploadEntityStyle
{
    /**
     * Styles for image files
     *
     * @var ImageStyle[]
     */
    protected array $imageStyles = [];

    /**
     * Styles for video files
     *
     * @var VideoStyle[]
     */
    protected array $videoStyles = [];

    /**
     * Styles for audio files
     *
     * @var AudioStyle[]
     */
    protected array $audioStyles = [];

    /**
     * Stream styles
     *
     * @var StreamStyle[]
     */
    protected array $streams = [];

    /**
     * Cover style
     *
     * @var ImageStyle
     */
    protected ImageStyle $coverStyle;


    public function getImageStyles(): array
    {
        return $this->imageStyles;
    }

    public function getVideoStyles(): array
    {
        return $this->videoStyles;
    }

    public function getAudioStyles(): array
    {
        return $this->audioStyles;
    }

    public function image(string $name, ?int $width = null, ?int $height = null, LaruploadMediaStyle $mode = LaruploadMediaStyle::AUTO): UploadEntities
    {
        $this->imageStyles[$name] = ImageStyle::make($name, $width, $height, $mode);

        return $this;
    }

    public function video(string $name, ?int $width = null, ?int $height = null, LaruploadMediaStyle $mode = LaruploadMediaStyle::SCALE_HEIGHT, X264 $format = new X264, bool $padding = false): UploadEntities
    {
        $this->videoStyles[$name] = VideoStyle::make($name, $width, $height, $mode, $format, $padding);

        return $this;
    }

    public function audio(string $name, Mp3|Aac|Wav|Flac $format = new Mp3, int $bitrate = 128): UploadEntities
    {
        $this->audioStyles[$name] = AudioStyle::make($name, $format, $bitrate);

        return $this;
    }

    public function stream(string $name, int $width, int $height, X264 $format, LaruploadMediaStyle $mode = LaruploadMediaStyle::SCALE_HEIGHT, bool $padding = false): UploadEntities
    {
        $this->streams[$name] = StreamStyle::make($name, $width, $height, $format, $mode, $padding);

        return $this;
    }

    public function coverStyle(string $name, ?int $width = null, ?int $height = null, LaruploadMediaStyle $mode = LaruploadMediaStyle::AUTO): UploadEntities
    {
        $this->coverStyle = ImageStyle::make($name, $width, $height, $mode);

        return $this;
    }

    protected function styleHasFile(string $style): bool
    {
        if (in_array($style, [Larupload::ORIGINAL_FOLDER, Larupload::COVER_FOLDER])) {
            return true;
        }

        $type = $this->output['type'];
        $types = [
            LaruploadFileType::VIDEO->name,
            LaruploadFileType::IMAGE->name,
            LaruploadFileType::AUDIO->name
        ];

        if (in_array($type, $types)) {
            $styles = match ($type) {
                LaruploadFileType::IMAGE->name => $this->imageStyles,
                LaruploadFileType::VIDEO->name => $this->videoStyles,
                LaruploadFileType::AUDIO->name => $this->audioStyles,
                default => throw new \InvalidArgumentException("Unsupported file type: $type")
            };

            return array_key_exists($style, $styles);
        }

        return false;
    }
}
