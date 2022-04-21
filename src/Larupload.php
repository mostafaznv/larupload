<?php

namespace Mostafaznv\Larupload;

use Exception;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
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
        $urls = $this->urls();

        $this->updateMeta($urls);

        return $urls;
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

    /**
     * Update Cover
     *
     * @param UploadedFile $file
     * @return object|null
     * @throws FileNotFoundException
     * @throws Exception
     */
    public function changeCover(UploadedFile $file): ?object
    {
        if ($this->metaIsExists()) {
            $this->internalFunctionIsCallable = true;
            $res = parent::updateCover($file);

            if ($res) {
                $this->setCover($this->id);
                $this->updateMeta();

                return $this->urls();
            }
        }

        return null;
    }

    /**
     * Delete Cover
     *
     * @return object|null
     * @throws FileNotFoundException
     * @throws Exception
     */
    public function deleteCover(): ?object
    {
        if ($this->metaIsExists()) {
            $this->internalFunctionIsCallable = true;
            $res = parent::detachCover();

            if ($res) {
                $this->setCover($this->id);
                $this->updateMeta();

                return $this->urls();
            }
        }

        return null;
    }

    protected function updateMeta(object $urls = null)
    {
        if (is_null($urls)) {
            $urls = $this->urls();
        }

        $metaPath = $this->getBasePath($this->id) . '/.meta';
        Storage::disk($this->disk)->put($metaPath, json_encode($urls), 'private');
    }

    /**
     * Check if .meta is exists or not
     *
     * @return bool
     */
    protected function metaIsExists(): bool
    {
        $metaPath = $this->getBasePath($this->id) . '/.meta';

        if (Storage::disk($this->disk)->exists($metaPath)) {
            $meta = Storage::disk($this->disk)->get($metaPath);
            $meta = json_decode($meta);

            foreach ($meta->meta as $key => $value) {
                $this->output[$key] = $value;
            }

            return true;
        }

        return false;
    }
}
