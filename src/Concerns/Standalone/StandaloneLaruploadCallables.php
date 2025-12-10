<?php

namespace Mostafaznv\Larupload\Concerns\Standalone;

use Mostafaznv\Larupload\Larupload;


trait StandaloneLaruploadCallables
{
    /**
     * @internal
     */
    public function url(string $style = Larupload::ORIGINAL_FOLDER): ?string
    {
        if ($this->internalFunctionIsCallable) {
            return parent::url($style);
        }

        $this->internalException();
    }

    /**
     * @internal
     */
    public function meta(?string $key = null): object|int|string|null
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
}
