<?php

namespace Mostafaznv\Larupload\Concerns\Standalone;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Mostafaznv\Larupload\LaruploadEnum;
use Mostafaznv\Larupload\UploadEntities;
use Symfony\Component\HttpFoundation\StreamedResponse;

trait StandaloneLaruploadNotCallables
{
    /**
     * @throws Exception
     * @internal
     */
    public static function make(string $name, string $mode = LaruploadEnum::HEAVY_MODE): UploadEntities
    {
        self::internalException();
    }

    /**
     * @throws Exception
     * @internal
     */
    public function saved(Model $model): Model
    {
        self::internalException();
    }

    /**
     * @throws Exception
     * @internal
     */
    public function deleted(Model $model): void
    {
        self::internalException();
    }

    /**
     * @throws Exception
     * @internal
     */
    public function download(string $style = 'original'): StreamedResponse|RedirectResponse|null
    {
        self::internalException();
    }

    /**
     * @throws Exception
     * @internal
     */
    public function setOutput(Model $model): void
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
        throw new Exception(
            'This function flagged as @internal and is not available on standalone uploader.'
        );
    }
}
