<?php

namespace Mostafaznv\Larupload\Concerns\Storage\UploadEntity;


use Mostafaznv\Larupload\DTOs\Stream;
use Mostafaznv\Larupload\DTOs\Style;
use Mostafaznv\Larupload\LaruploadEnum;
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
        if (in_array($style, [LaruploadEnum::ORIGINAL_FOLDER, LaruploadEnum::COVER_FOLDER])) {
            return true;
        }

        if (array_key_exists($style, $this->styles)) {
            $type = $this->output['type'];

            if (in_array($type, [LaruploadEnum::VIDEO, LaruploadEnum::IMAGE])) {
                $styleTypes = $this->styles[$style]->type;

                return count($styleTypes) == 0 or in_array($type, $styleTypes);
            }
        }

        return false;
    }
}
