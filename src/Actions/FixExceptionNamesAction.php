<?php

namespace Mostafaznv\Larupload\Actions;

use Illuminate\Support\Str;
use Mostafaznv\Larupload\DTOs\Style\Style;
use Mostafaznv\Larupload\Larupload;


/**
 * In some cases, we should use different file names instead of the original.
 *
 * Example: when a user uploads an SVG image, and it is converted to JPG,
 * the file name must be adjusted accordingly.
 */
readonly class FixExceptionNamesAction
{
    public function __construct(
        private string $name,
        private string $styleName,
        private ?Style $style = null,
    ) {}

    public static function make(string $name, string $styleName, ?Style $style = null): self
    {
        return new self($name, $styleName, $style);
    }


    public function run(): string
    {
        $name = $this->name;

        if ($this->style) {
            $name = larupload_style_path($name, $this->style->extension());
        }

        if (!in_array($this->styleName, [Larupload::ORIGINAL_FOLDER, Larupload::COVER_FOLDER])) {
            if (Str::endsWith($name, 'svg')) {
                return str_replace('svg', 'jpg', $name);
            }
        }

        return $name;
    }
}
