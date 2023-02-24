<?php

namespace Mostafaznv\Larupload\Helpers;

use Illuminate\Http\UploadedFile;
use Mostafaznv\Larupload\Enums\LaruploadFileType;
use Mostafaznv\Larupload\Enums\LaruploadMode;
use Mostafaznv\Larupload\LaruploadEnum;

trait LaraTools
{
    /**
     * Get file type
     *
     * @param UploadedFile $file
     * @return LaruploadFileType|null
     */
    protected function getFileType(UploadedFile $file): ?LaruploadFileType
    {
        if ($file->isValid()) {
            $mime = $file->getMimeType();

            return $this->mimeToType($mime);
        }

        return null;
    }

    /**
     * Convert mimetype to human-readable type
     *
     * @param string|null $mime
     * @return LaruploadFileType
     */
    protected function mimeToType(string $mime = null): LaruploadFileType
    {
        if (str_contains($mime, 'image/')) {
            return LaruploadFileType::IMAGE;
        }
        else if (str_contains($mime, 'video/')) {
            return LaruploadFileType::VIDEO;
        }
        else if (str_contains($mime, 'audio/')) {
            return LaruploadFileType::AUDIO;
        }
        else if ($mime == 'application/pdf') {
            return LaruploadFileType::PDF;
        }
        else if ($mime == 'application/zip' or $mime == 'application/x-rar-compressed') {
            return LaruploadFileType::COMPRESSED;
        }

        return LaruploadFileType::FILE;
    }

    protected function isImage(UploadedFile $file): bool
    {
        return $this->mimeToType($file->getMimeType()) === LaruploadFileType::IMAGE;
    }

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

    /**
     * Get temp directory
     *
     * @return string
     */
    protected function tempDir(): string
    {
        if (ini_get('upload_tmp_dir')) {
            $path = ini_get('upload_tmp_dir');
        }
        else if (getenv('temp')) {
            $path = getenv('temp');
        }
        else {
            $path = sys_get_temp_dir();
        }

        return rtrim($path, '/');
    }

    /**
     * Extract name from path
     *
     * @param string $dir
     * @return array
     */
    protected function splitPath(string $dir): array
    {
        $path = dirname($dir);
        $name = pathinfo($dir, PATHINFO_BASENAME);

        return [$path, $name];
    }

    /**
     * Check if given driver is local
     *
     * @param $disk
     * @return bool
     */
    protected function diskDriverIsLocal($disk): bool
    {
        return config("filesystems.disks.$disk.driver") == LaruploadEnum::LOCAL_DRIVER;
    }
}
