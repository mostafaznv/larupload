<?php

namespace Mostafaznv\Larupload\DTOs\Style;

use Exception;
use Mostafaznv\Larupload\Enums\Style\LaruploadImageStyleMode;

class ImageStyle extends Style
{
    public readonly LaruploadImageStyleMode $mode;

    public function __construct(string $name, ?int $width = null, ?int $height = null, LaruploadImageStyleMode $mode = LaruploadImageStyleMode::AUTO)
    {
        parent::__construct($name, $width, $height);

        $this->mode = $mode;

        $this->validateDimension();
    }

    public static function make(string $name, ?int $width = null, ?int $height = null, LaruploadImageStyleMode $mode = LaruploadImageStyleMode::AUTO): self
    {
        return new self($name, $width, $height, $mode);
    }


    private function validateDimension(): void
    {
        if ($this->mode === LaruploadImageStyleMode::LANDSCAPE) {
            if ($this->width === null or $this->width === 0) {
                throw new Exception(
                    'Width is required when you are in landscape mode'
                );
            }
        }
        else if ($this->mode === LaruploadImageStyleMode::PORTRAIT) {
            if ($this->height === null or $this->height === 0) {
                throw new Exception(
                    'Height is required when you are in portrait mode'
                );
            }
        }
        else if (in_array($this->mode, [LaruploadImageStyleMode::CROP, LaruploadImageStyleMode::EXACT])) {
            if (!$this->width or !$this->height) {
                throw new Exception(
                    'Width and Height are required when you are in crop/exact mode'
                );
            }
        }
        else if ($this->mode === LaruploadImageStyleMode::AUTO) {
            if (!$this->width and !$this->height) {
                throw new Exception(
                    'Width and height are required when you are in auto mode'
                );
            }
        }
    }
}
