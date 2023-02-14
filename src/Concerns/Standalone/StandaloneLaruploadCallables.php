<?php

namespace Mostafaznv\Larupload\Concerns\Standalone;

use Exception;
use Mostafaznv\Larupload\LaruploadEnum;

trait StandaloneLaruploadCallables
{
    /**
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
     * @throws Exception
     * @internal
     */
    public function meta(string $key = null): object|int|string|null
    {
        if ($this->internalFunctionIsCallable) {
            return parent::meta($key);
        }

        self::internalException();
    }

    /**
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
    public function handleFFMpegQueue(bool $isLastOne = false): void
    {
        if ($this->internalFunctionIsCallable) {
            parent::handleFFMpegQueue($isLastOne);
        }
        else {
            self::internalException();
        }
    }
}
