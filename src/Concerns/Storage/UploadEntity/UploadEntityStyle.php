<?php

namespace Mostafaznv\Larupload\Concerns\Storage\UploadEntity;


use Mostafaznv\Larupload\DTOs\Stream;
use Mostafaznv\Larupload\DTOs\Style\ImageStyle;
use Mostafaznv\Larupload\DTOs\Style\VideoStyle;
use Mostafaznv\Larupload\Enums\LaruploadFileType;
use Mostafaznv\Larupload\Enums\Style\LaruploadImageStyleMode;
use Mostafaznv\Larupload\Enums\Style\LaruploadVideoStyleMode;
use Mostafaznv\Larupload\Larupload;
use Mostafaznv\Larupload\UploadEntities;

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
     * Stream styles
     *
     * @var Stream[]
     */
    protected array $streams = [];

    /**
     * Cover style
     *
     * @var ImageStyle|null
     */
    protected ?ImageStyle $coverStyle = null;


    public function image(string $name, ?int $width = null, ?int $height = null, LaruploadImageStyleMode $mode = LaruploadImageStyleMode::AUTO): UploadEntities
    {
        $this->imageStyles[$name] = ImageStyle::make($name, $width, $height, $mode);

        return $this;
    }

    public function video(string $name, ?int $width = null, ?int $height = null, LaruploadVideoStyleMode $mode = LaruploadVideoStyleMode::SCALE_HEIGHT): UploadEntities
    {
        $this->videoStyles[$name] = VideoStyle::make($name, $width, $height, $mode);

        return $this;
    }

    public function stream(Stream $stream): UploadEntities
    {
        $this->streams[$stream->name] = $stream;

        return $this;
    }

    public function coverStyle(string $name, ?int $width = null, ?int $height = null, LaruploadImageStyleMode $mode = LaruploadImageStyleMode::AUTO): UploadEntities
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
            LaruploadFileType::IMAGE->name
        ];

        if (in_array($type, $types)) {
            $styles = $type === LaruploadFileType::IMAGE->name
                ? $this->imageStyles
                : $this->videoStyles;

            return array_key_exists($style, $styles);
        }

        return false;
    }
}
