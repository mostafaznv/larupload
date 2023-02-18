<?php

namespace Mostafaznv\Larupload\Concerns\Standalone;

use Exception;
use Mostafaznv\Larupload\LaruploadEnum;

trait StandaloneLaruploadCallables
{
    /**
     * @internal
     */
    public function url(string $style = LaruploadEnum::ORIGINAL_FOLDER): ?string
    {
        if ($this->internalFunctionIsCallable) {
            return parent::url($style);
        }

        $this->internalException();
    }

    /**
     * @internal
     */
    public function meta(string $key = null): object|int|string|null
    {
        if ($this->internalFunctionIsCallable) {
            return parent::meta($key);
        }

        $this->internalException();
    }

    /**
     * @internal
     */
    public function urls(): object
    {
        if ($this->internalFunctionIsCallable) {
            return parent::urls();
        }

        $this->internalException();
    }

    /**
     * @internal
     */
    public function handleFFMpegQueue(bool $isLastOne = false): void
    {
        if ($this->internalFunctionIsCallable) {
            parent::handleFFMpegQueue($isLastOne);
        }
        else {
            $this->internalException();
        }
    }
}
