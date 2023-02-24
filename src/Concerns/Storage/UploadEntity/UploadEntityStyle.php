<?php

namespace Mostafaznv\Larupload\Concerns\Storage\UploadEntity;


use Mostafaznv\Larupload\DTOs\Stream;
use Mostafaznv\Larupload\DTOs\Style;
use Mostafaznv\Larupload\Enums\LaruploadFileType;
use Mostafaznv\Larupload\Larupload;
use Mostafaznv\Larupload\UploadEntities;

trait UploadEntityStyle
{
    /**
     * Styles for image/video files
     *
     * @var Style[]
     */
    protected array $styles = [];

    /**
     * Stream styles
     *
     * @var Stream[]
     */
    protected array $streams = [];

    /**
     * Cover style
     *
     * @var Style|null
     */
    protected ?Style $coverStyle = null;


    public function style(Style $style): UploadEntities
    {
        $this->styles[$style->name] = $style;

        return $this;
    }

    public function stream(Stream $stream): UploadEntities
    {
        $this->streams[$stream->name] = $stream;

        return $this;
    }

    public function coverStyle(Style $style): UploadEntities
    {
        $this->coverStyle = $style;

        return $this;
    }

    protected function styleHasFile(string $style): bool
    {
        if (in_array($style, [Larupload::ORIGINAL_FOLDER, Larupload::COVER_FOLDER])) {
            return true;
        }

        if (array_key_exists($style, $this->styles)) {
            $type = $this->output['type'];
            $types = [
                LaruploadFileType::VIDEO->name,
                LaruploadFileType::IMAGE->name
            ];

            if (in_array($type, $types)) {
                $styleTypes = enum_to_names($this->styles[$style]->type);

                return count($styleTypes) == 0 or in_array($type, $styleTypes);
            }
        }

        return false;
    }
}
