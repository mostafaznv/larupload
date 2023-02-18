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
        $self = new self();
        $self->internalException();
    }

    /**
     * @internal
     */
    public function saved(Model $model): Model
    {
        $this->internalException();
    }

    /**
     * @internal
     */
    public function deleted(Model $model): void
    {
        $this->internalException();
    }

    /**
     * @internal
     */
    public function download(string $style = 'original'): StreamedResponse|RedirectResponse|null
    {
        $this->internalException();
    }

    /**
     * @internal
     */
    public function setOutput(Model $model): void
    {
        $this->internalException();
    }

    private function internalException()
    {
        throw new Exception(
            'This function is flagged as @internal and is not available on the standalone uploader.'
        );
    }
}
