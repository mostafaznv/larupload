<?php

namespace Mostafaznv\Larupload\Exceptions;

use Exception;


class InvalidImageOptimizerException extends Exception
{
    public function __construct(string $optimizerClass, string $optimizerInterface)
    {
        parent::__construct(
            "Configured optimizer [$optimizerClass] does not implement [$optimizerInterface]."
        );
    }
}
