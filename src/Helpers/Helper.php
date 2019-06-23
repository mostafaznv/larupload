<?php

namespace Mostafaznv\Larupload\Helpers;

use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class Helper
{
    /**
     * Return Original config file.
     *
     * @param null $key
     * @return array|mixed
     */
    public static function originalConfig($key = null)
    {
        $path = null;

        if (is_file(base_path('vendor/mostafaznv/larupload/config/config.php')))
            $path = base_path('vendor/mostafaznv/larupload/config/config.php');
        else if (is_file(base_path('packages/mostafaznv/larupload/config/config.php')))
            $path = base_path('packages/mostafaznv/larupload/config/config.php');

        if ($path) {
            $config = File::getRequire(base_path('packages/mostafaznv/larupload/config/config.php'));
            if ($key and isset($config[$key]))
                return $config[$key];
            return $config;
        }
        return [];
    }

    /**
     * Merge two array recursively.
     *
     * @param array $array1
     * @param array $array2
     * @return array
     */
    public static function arrayMergeRecursiveDistinct(array $array1, array $array2)
    {
        $merged = $array1;

        foreach ($array2 as $key => $value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = self::arrayMergeRecursiveDistinct($merged[$key], $value);
            }
            else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }

    /**
     * Validate files options.
     *
     * @param array $config
     * @return bool
     * @throws Exception
     */
    public static function validate(Array $config = [])
    {
        $rules = [
            'storage'       => 'string',
            'mode'          => 'string|in:light,heavy',
            'naming_method' => 'string|in:slug,hash_file,time',

            'styles'          => 'array',
            'styles.*'        => 'array',
            'styles.*.height' => 'numeric|nullable|required_if:styles.*.mode,crop',
            'styles.*.width'  => 'numeric|nullable|required_if:styles.*.mode,crop',
            'styles.*.mode'   => 'string:in:landscape,portrait,crop,exact,auto',
            'styles.*.type'   => 'array:in:image,video',

            'dominant_color'     => 'boolean',
            'generate_cover'     => 'boolean',
            'cover_style'        => 'array',
            'cover_style.width'  => 'numeric',
            'cover_style.height' => 'numeric',
            'cover_style.mode'   => 'string:in:landscape,portrait,crop,exact,auto',

            'keep_old_files'     => 'boolean',
            'preserve_files'     => 'boolean',
            'allowed_mime_types' => 'array',
            'allowed_mimes'      => 'array',

            'ffmpeg-capture-frame' => 'nullable|between:0,99999.99',
            'ffmpeg-timeout'       => 'nullable|numeric',
            'ffmpeg-queue'         => 'nullable|boolean',
            'ffmpeg-max-queue-num' => 'nullable|numeric',
        ];

        $validator = Validator::make($config, $rules);

        if ($validator->fails()) {
            $errors = $validator->errors()->getMessages();
            $fields = implode(', ', array_keys($errors));

            throw new Exception("invalid fields: $fields");
        }

        return true;
    }

    /**
     * Convert disk name to driver name.
     *
     * @param $disk
     * @return \Illuminate\Config\Repository|mixed
     */
    public static function diskToDriver($disk)
    {
        return config("filesystems.disks.$disk.driver");
    }

    /**
     * Get temp directory.
     *
     * @return array|false|string
     */
    public static function tempDir()
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
     * @param $dir
     * @return array
     */
    public static function splitPath($dir)
    {
        $path = dirname($dir);
        $name = pathinfo($dir, PATHINFO_BASENAME);

        return [$path, $name];
    }
}