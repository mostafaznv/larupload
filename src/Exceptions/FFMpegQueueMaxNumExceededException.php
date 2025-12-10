<?php

namespace Mostafaznv\Larupload\Exceptions;

use Exception;


class FFMpegQueueMaxNumExceededException extends Exception
{
    public function __construct()
    {
        parent::__construct(
            trans('larupload::messages.max-queue-num-exceeded')
        );
    }
}
