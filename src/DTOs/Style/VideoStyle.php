<?php

namespace Mostafaznv\Larupload\DTOs\Style;

use Exception;
use Mostafaznv\Larupload\Enums\Style\LaruploadVideoStyleMode;

class VideoStyle extends Style
{
    public readonly LaruploadVideoStyleMode $mode;

    public function __construct(string $name, ?int $width = null, ?int $height = null, LaruploadVideoStyleMode $mode = LaruploadVideoStyleMode::SCALE_HEIGHT,)
    {
        parent::__construct($name, $width, $height);

        $this->mode = $mode;

        $this->validateDimension();
    }

    public static function make(string $name, ?int $width = null, ?int $height = null, LaruploadVideoStyleMode $mode = LaruploadVideoStyleMode::SCALE_HEIGHT): self
    {
        return new self($name, $width, $height, $mode);
    }


    private function validateDimension(): void
    {
        if ($this->mode === LaruploadVideoStyleMode::SCALE_HEIGHT) {
            if ($this->width === null or $this->width === 0) {
                throw new Exception(
                    'Width is required when you are in SCALE_HEIGHT mode'
                );
            }
        }
        else if ($this->mode === LaruploadVideoStyleMode::SCALE_WIDTH) {
            if ($this->height === null or $this->height === 0) {
                throw new Exception(
                    'Height is required when you are in SCALE_WIDTH mode'
                );
            }
        }
        else if (in_array($this->mode, [LaruploadVideoStyleMode::CROP, LaruploadVideoStyleMode::FIT])) {
            if (!$this->width or !$this->height) {
                throw new Exception(
                    'Width and Height are required when you are in CROP/FIT mode'
                );
            }
        }
        else if ($this->mode === LaruploadVideoStyleMode::INSET) {
            if (!$this->width and !$this->height) {
                throw new Exception(
                    'Width or height are required when you are in exact mode'
                );
            }
        }
    }
}
