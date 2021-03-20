<?php

namespace Mostafaznv\Larupload\Helpers;

use Illuminate\Http\UploadedFile;
use Mostafaznv\Larupload\LaruploadEnum;
use Illuminate\Support\Str;

trait LaraTools
{
    /**
     * Get file type
     *
     * @param UploadedFile $file
     * @return string
     */
    protected function getFileType(UploadedFile $file): string
    {
        if ($file and $file->isValid()) {
            $mime = $file->getMimeType();

            return $this->mimeToType($mime);
        }

        return '';
    }

    /**
     * Convert mimetype to human readable type
     *
     * @param string $mime
     * @return string
     */
    protected function mimeToType(string $mime): string
    {
        if (strstr($mime, 'image/')) {
            return LaruploadEnum::IMAGE;
        }
        else if (strstr($mime, 'video/')) {
            return LaruploadEnum::VIDEO;
        }
        else if (strstr($mime, 'audio/')) {
            return LaruploadEnum::AUDIO;
        }
        else if ($mime == 'application/pdf') {
            return LaruploadEnum::PDF;
        }
        else if ($mime == 'application/zip' or $mime == 'application/x-rar-compressed') {
            return LaruploadEnum::COMPRESSED;
        }

        return LaruploadEnum::FILE;
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
        $path = "{$this->folder}/$id/{$this->nameKebab}";

        if ($folder) {
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
     * Convert disk name to driver name
     *
     * @param string $disk
     * @return string
     */
    public function diskToDriver(string $disk): string
    {
        return config("filesystems.disks.$disk.driver");
    }

    /**
     * Get temp directory
     *
     * @return string
     */
    public function tempDir(): string
    {
        if (ini_get('upload_tmp_dir')) {
            return ini_get('upload_tmp_dir');
        }
        else if (getenv('temp')) {
            return getenv('temp');
        }
        else {
            return sys_get_temp_dir();
        }
    }

    /**
     * Extract name from path
     *
     * @param string $dir
     * @return array
     */
    public function splitPath(string $dir): array
    {
        $path = dirname($dir);
        $name = pathinfo($dir, PATHINFO_BASENAME);

        return [$path, $name];
    }
}
