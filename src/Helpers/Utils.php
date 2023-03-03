<?php

if (!function_exists('enum_to_names')) {
    /**
     * Returns an array of enum names
     *
     * @param UnitEnum[] $enums
     * @return array
     */
    function enum_to_names(array $enums): array
    {
        return array_column($enums, 'name');
    }
}

if (!function_exists('disk_driver_is_local')) {
    /**
     * Check if given driver is local
     *
     * @param string $disk
     * @return bool
     */
    function disk_driver_is_local(string $disk): bool
    {
        return config("filesystems.disks.$disk.driver") == \Mostafaznv\Larupload\Larupload::LOCAL_DRIVER;
    }
}

if (!function_exists('larupload_temp_dir')) {
    /**
     * Get temp directory
     *
     * @return string
     */
    function larupload_temp_dir(): string
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
}

if (!function_exists('split_larupload_path')) {
    /**
     * Extract name from path
     *
     * @param string $dir
     * @return array
     */
    function split_larupload_path(string $dir): array
    {
        $path = dirname($dir);
        $name = pathinfo($dir, PATHINFO_BASENAME);

        return [$path, $name];
    }
}

if (!function_exists('get_larupload_save_path')) {
    /**
     * Get save to path
     *
     * @param string $disk
     * @param string $saveTo
     * @return array
     */
    function get_larupload_save_path(string $disk, string $saveTo): array
    {
        $permanent = \Illuminate\Support\Facades\Storage::disk($disk)->path($saveTo);
        list($path, $name) = split_larupload_path($saveTo);

        if (disk_driver_is_local($disk)) {
            $temp = null;
        }
        else {
            $tempDir = larupload_temp_dir();
            $tempName = time() . '-' . $name;
            $temp = "$tempDir/$tempName";
        }

        return [
            'path'      => $path,
            'name'      => $name,
            'temp'      => $temp,
            'local'     => $temp ?: $permanent,
            'permanent' => $permanent,
        ];
    }
}

if (!function_exists('larupload_finalize_save')) {
    /**
     * Upload local files/folders to remote server, if driver was not local
     *
     * @param string $disk
     * @param array $saveTo
     * @param bool $isFolder
     * @return void
     */
    function larupload_finalize_save(string $disk, array $saveTo, bool $isFolder = false): void
    {
        if ($saveTo['temp']) {
            if ($isFolder) {
                $path = $saveTo['temp'];
                $rii = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($path)
                );


                foreach ($rii as $r) {
                    if ($r->isFile()) {
                        $filePath = $r->getPathname();
                        $subPath = str_replace("$path/", '', $filePath);
                        $saveToPath = dirname($saveTo['path'] . '/' . $saveTo['name'] . '/' . $subPath);

                        $fileObject = new \Symfony\Component\HttpFoundation\File\File($filePath);

                        \Illuminate\Support\Facades\Storage::disk($disk)->putFileAs(
                            $saveToPath, $fileObject, $fileObject->getFilename()
                        );

                        unset($fileObject);
                    }
                }

                @rmdir($path);
            }
            else {
                $file = new \Symfony\Component\HttpFoundation\File\File($saveTo['temp']);

                \Illuminate\Support\Facades\Storage::disk($disk)->putFileAs(
                    $saveTo['path'], $file, $saveTo['name']
                );

                @unlink($saveTo['temp']);
            }
        }
    }
}
