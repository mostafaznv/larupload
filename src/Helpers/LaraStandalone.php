<?php

namespace Mostafaznv\Larupload\Helpers;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Mostafaznv\Larupload\LaruploadEnum;
use Mostafaznv\Larupload\UploadEntities;
use Symfony\Component\HttpFoundation\StreamedResponse;

trait LaraStandalone
{
    /**
     * @param string $style
     * @return null|string
     * @throws Exception
     * @internal
     */
    public function url(string $style = LaruploadEnum::ORIGINAL_FOLDER): ?string
    {
        if ($this->internalFunctionIsCallable) {
            return parent::url($style);
        }

        self::internalException();
    }

    /**
     * @param string|null $key
     * @return object|string|integer|null
     * @throws Exception
     * @internal
     */
    public function meta(string $key = null)
    {
        if ($this->internalFunctionIsCallable) {
            return parent::meta($key);
        }

        self::internalException();
    }

    /**
     * @return object
     * @throws Exception
     * @internal
     */
    public function urls(): object
    {
        if ($this->internalFunctionIsCallable) {
            return parent::urls();
        }

        self::internalException();
    }

    /**
     * @throws Exception
     * @internal
     */
    public function handleFFMpegQueue(): void
    {
        if ($this->internalFunctionIsCallable) {
            parent::handleFFMpegQueue();
        }
        else {
            self::internalException();
        }
    }

    /**
     * @param UploadedFile $file
     * @return bool
     * @throws Exception
     * @internal
     */
    public function updateCover(UploadedFile $file): bool
    {
        if ($this->internalFunctionIsCallable) {
            return parent::updateCover($file);
        }

        self::internalException();
    }

    /****** not call callable ******************************************************************************************/

    /**
     * @param string $name
     * @param string $mode
     * @return UploadEntities
     * @throws Exception
     * @internal
     */
    public static function make(string $name, string $mode = LaruploadEnum::HEAVY_MODE): UploadEntities
    {
        self::internalException();
    }

    /**
     * @param mixed $file
     * @param null $cover
     * @return bool
     * @throws Exception
     * @internal
     */
    public function setUploadedFile($file, $cover = null): bool
    {
        self::internalException();
    }

    /**
     * @param Model $model
     * @return Model
     * @throws Exception
     * @internal
     */
    public function saved(Model $model): Model
    {
        self::internalException();
    }

    /**
     * @param Model $model
     * @throws Exception
     * @internal
     */
    public function deleted(Model $model): void
    {
        self::internalException();
    }

    /**
     * @param string $style
     * @return RedirectResponse|StreamedResponse|null
     * @throws Exception
     * @internal
     */
    public function download(string $style = 'original')
    {
        self::internalException();
    }

    /**
     * @param Model $model
     * @throws Exception
     * @internal
     */
    public function setOutput(Model $model)
    {
        self::internalException();
    }

    /**
     * Throw exception for internal functions
     *
     * @throws Exception
     */
    protected static function internalException()
    {
        throw new Exception('This function flagged as @internal and is not available on standalone uploader.');
    }
}
