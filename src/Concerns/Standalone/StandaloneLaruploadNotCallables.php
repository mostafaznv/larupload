<?php

namespace Mostafaznv\Larupload\Concerns\Standalone;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Mostafaznv\Larupload\Enums\LaruploadMode;
use Mostafaznv\Larupload\Enums\LaruploadSecureIdsMethod;
use Mostafaznv\Larupload\UploadEntities;
use Symfony\Component\HttpFoundation\StreamedResponse;


trait StandaloneLaruploadNotCallables
{
    /**
     * @throws Exception
     * @internal
     */
    public static function make(string $name, LaruploadMode $mode = LaruploadMode::HEAVY): UploadEntities
    {
        $self = new self($name, $mode);
        $self->internalException();
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

    /**
     * @internal
     */
    public function secureIdsMethod(LaruploadSecureIdsMethod $method): self
    {
        $this->internalException();
    }

    /**
     * @internal
     */
    public function handleFFMpegQueue(bool $isLastOne = false, bool $standalone = false): void
    {
        $this->internalException();
    }

    private function internalException()
    {
        throw new Exception('This function is flagged as @internal and is not available on the standalone uploader.');
    }
}
