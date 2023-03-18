<?php

namespace Mostafaznv\Larupload\Helpers;

use Illuminate\Http\UploadedFile;
use Mostafaznv\Larupload\Enums\LaruploadMode;

trait LaraTools
{
    /**
     * Path Helper to generate relative path string
     *
     * @param int $id
     * @param string|null $folder
     * @return string
     */
    protected function getBasePath(int $id, string $folder = null): string
    {
        $path = $this->mode == LaruploadMode::STANDALONE ? "$this->folder/$this->nameKebab" : "$this->folder/$id/$this->nameKebab";
        $path = trim($path, '/');

        if ($folder) {
            $folder = strtolower(str_replace('_', '-', trim($folder)));

            return "$path/$folder";
        }

        return $path;
    }

    /**
     * Check file is set and has value
     *
     * @param $file
     * @return bool
     */
    protected function fileIsSetAndHasValue($file): bool
    {
        return $file and ($file instanceof UploadedFile);
    }
}
