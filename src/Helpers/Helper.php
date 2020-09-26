<?php

namespace Mostafaznv\Larupload\Helpers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class Helper
{
    /**
     * Return Original config file
     *
     * @param string $key
     * @return array|mixed
     */
    public static function originalConfig(string $key = null)
    {
        $path = null;

        if (is_file(base_path('vendor/mostafaznv/larupload/config/config.php'))) {
            $path = base_path('vendor/mostafaznv/larupload/config/config.php');
        }
        else if (is_file(base_path('packages/mostafaznv/larupload/config/config.php'))) {
            $path = base_path('packages/mostafaznv/larupload/config/config.php');
        }

        if ($path) {
            $config = File::getRequire(base_path('packages/mostafaznv/larupload/config/config.php'));

            if ($key and isset($config[$key])) {
                return $config[$key];
            }

            return $config;
        }

        return [];
    }

    /**
     * Merge two array recursively
     *
     * @param array $array1
     * @param array $array2
     * @return array
     */
    public static function arrayMergeRecursiveDistinct(array $array1, array $array2): array
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
     * Validate files options
     *
     * @param array $config
     * @return array
     */
    public static function validate(array $config = []): array
    {
        $rules = [
            'storage'       => 'nullable|string',
            'mode'          => 'string|in:light,heavy',
            'with_meta'     => 'nullable|boolean',
            'naming_method' => 'string|in:slug,hash_file,time',

            'styles'          => 'array|min:1',
            'styles.*'        => ['array', 'min:1', function($attribute, $value, $fail) {
                $key = str_replace('styles.', '', $attribute);
                return self::notNumericKeyRule($attribute, $key, $fail);
            }],
            'styles.*.height' => 'numeric|nullable|required_if:styles.*.mode,crop',
            'styles.*.width'  => 'numeric|nullable|required_if:styles.*.mode,crop',
            'styles.*.mode'   => 'string|nullable|in:landscape,portrait,crop,exact,auto',
            'styles.*.type'   => 'array|nullable|in:image,video',

            'styles.stream'                 => 'nullable|array|min:1',
            'styles.stream.*'               => ['array', 'min:1', function($attribute, $value, $fail) {
                $key = str_replace('styles.stream.', '', $attribute);
                return self::notNumericKeyRule($attribute, $key, $fail);
            }],
            'styles.stream.*.height'        => 'required|numeric',
            'styles.stream.*.width'         => 'required|numeric',
            'styles.stream.*.bitrate'       => 'required|array|min:1',
            'styles.stream.*.bitrate.audio' => ['required', function($attribute, $value, $fail) {
                return self::numericBitrateRule($attribute, $value, $fail);
            }],
            'styles.stream.*.bitrate.video' => ['required', function($attribute, $value, $fail) {
                return self::numericBitrateRule($attribute, $value, $fail);
            }],

            'dominant_color'     => 'nullable|boolean',
            'generate_cover'     => 'nullable|boolean',
            'cover_style'        => 'nullable|array',
            'cover_style.width'  => 'nullable|numeric|required_if:cover_style.mode,crop',
            'cover_style.height' => 'nullable|numeric|required_if:cover_style.mode,crop',
            'cover_style.mode'   => 'string|in:landscape,portrait,crop,exact,auto',

            'keep_old_files'     => 'nullable|boolean',
            'preserve_files'     => 'nullable|boolean',
            'allowed_mime_types' => 'nullable|array',
            'allowed_mimes'      => 'nullable|array',

            'ffmpeg-capture-frame' => 'nullable|numeric|min:0|between:0,99999.99',
            'ffmpeg-timeout'       => 'nullable|numeric|min:0',
            'ffmpeg-queue'         => 'nullable|boolean',
            'ffmpeg-max-queue-num' => 'nullable|numeric|min:1',
        ];

        $validator = Validator::make($config, $rules);

        if ($validator->fails()) {
            return $validator->errors()->getMessages();
        }

        return [];
    }

    /**
     * Validation rule to check attribute is not numeric
     *
     * @param $attribute
     * @param $key
     * @param $fail
     * @return mixed
     */
    public static function notNumericKeyRule($attribute, $key, $fail)
    {
        if (is_numeric($key)) {
            return $fail($attribute . ' is not a valid string');
        }
    }

    /**
     * Validation rule to check attribute is a valid bitrate
     *
     * @param $attribute
     * @param $value
     * @param $fail
     * @return mixed
     */
    public static function numericBitrateRule($attribute, $value, $fail)
    {
        $units = ['k', 'm'];
        $value = str_ireplace($units, '', $value);

        if (!is_numeric($value)) {
            return $fail($attribute . ' is not a valid bitrate');
        }
    }

    /**
     * Convert disk name to driver name
     *
     * @param string $disk
     * @return string
     */
    public static function diskToDriver(string $disk): string
    {
        return config("filesystems.disks.$disk.driver");
    }

    /**
     * Get temp directory
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
     * @param string $dir
     * @return array
     */
    public static function splitPath(string $dir): array
    {
        $path = dirname($dir);
        $name = pathinfo($dir, PATHINFO_BASENAME);

        return [$path, $name];
    }
}
