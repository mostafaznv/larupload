<?php

namespace Mostafaznv\Larupload;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mostafaznv\Larupload\Helpers\LaraStandalone;
use Mostafaznv\Larupload\Storage\Attachment;

class Larupload extends Attachment
{
    use LaraStandalone;

    /**
     * Holds the hash value for the current LARUPLOAD_NULL constant
     *
     * @var string
     */
    protected static string $laruploadNull;

    /**
     * Specify if internal functions are callable or not
     *
     * @var bool
     */
    protected bool $internalFunctionIsCallable = false;

    public function __construct(string $name, string $mode)
    {
        static::$laruploadNull = crc32(time());

        if (!defined('LARUPLOAD_NULL')) {
            define('LARUPLOAD_NULL', static::$laruploadNull);
        }

        parent::__construct($name, LaruploadEnum::STANDALONE_MODE);
    }

    /**
     * Init uploader
     *
     * @param string $name
     * @return Larupload
     */
    public static function init(string $name): Larupload
    {
        $instance = new self($name, LaruploadEnum::STANDALONE_MODE);
        $instance->id = time();

        return $instance;
    }

    /**
     * Upload file and cover
     *
     * @param UploadedFile $file
     * @param UploadedFile|null $cover
     * @return object
     * @throws Exception
     */
    public function upload(UploadedFile $file, UploadedFile $cover = null): object
    {
        $this->internalFunctionIsCallable = true;

        $this->file = $file;
        $this->type = $this->getFileType($file);
        $this->cover = $cover;

        $this->clean($this->id);
        $this->setBasicDetails();
        $this->setMediaDetails();
        $this->uploadOriginalFile($this->id);
        $this->setCover($this->id);
        $this->handleStyles($this->id, self::class, true);

        return $this->urls();
    }

    /**
     * Delete uploaded folder
     *
     * @return bool
     */
    public function delete(): bool
    {
        $basePath = $this->getBasePath($this->id);

        if (Storage::disk($this->disk)->exists($basePath)) {
            $this->clean($this->id);

            return true;
        }

        return false;
    }

}
