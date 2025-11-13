<?php

namespace Mostafaznv\Larupload\Actions;

use Illuminate\Support\Str;
use Mostafaznv\Larupload\DTOs\Style\Style;
use Mostafaznv\Larupload\Larupload;

/**
 * In some special cases we should use other file names instead of the original one.
 *
 * Example: when user uploads a svg image, we should change the converted format to jpg!
 * so we have to manipulate file name
 */
class FixExceptionNamesAction
{
    public function __construct(
        private readonly string $name,
        private readonly string $styleName,
        private readonly ?Style $style = null,
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
