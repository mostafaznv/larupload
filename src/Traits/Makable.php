<?php

namespace Mostafaznv\Larupload\Traits;


trait Makable
{
    public static function make(...$arguments): static
    {
        return new static(...$arguments);
    }
}
