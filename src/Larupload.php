<?php

namespace Mostafaznv\Larupload;

use Illuminate\Http\UploadedFile;
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

    protected bool $internalFunctionIsCallable = false;

    public function __construct(string $name, string $mode)
    {
        static::$laruploadNull = crc32(time());

        if (!defined('LARUPLOAD_NULL')) {
            define('LARUPLOAD_NULL', static::$laruploadNull);
        }

        parent::__construct($name, LaruploadEnum::STANDALONE_MODE);
    }

    public static function init(string $name): Larupload
    {
        $instance = new self($name, LaruploadEnum::STANDALONE_MODE);
        $instance->id = time();

        return $instance;
    }

    public function upload(UploadedFile $file, UploadedFile $cover = null)
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

}
