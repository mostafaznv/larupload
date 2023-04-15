<?php

namespace Mostafaznv\Larupload\Actions;

use Illuminate\Support\Str;
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
        private readonly string $style
    ) {}

    public static function make(string $name, string $style): self
    {
        return new self($name, $style);
    }


    public function run(): string
    {
        if (!in_array($this->style, [Larupload::ORIGINAL_FOLDER, Larupload::COVER_FOLDER])) {
            if (Str::endsWith($this->name, 'svg')) {
                return str_replace('svg', 'jpg', $this->name);
            }
        }

        return $this->name;
    }
}
