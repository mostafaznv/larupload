<?php

namespace Mostafaznv\Larupload\Enums;

enum LaruploadImageLibrary
{
    case GD;
    case IMAGICK;
    case GMAGICK;

    public function namespace(): string
    {
        return match ($this) {
            self::GD      => 'Imagine\Gd\Imagine',
            self::IMAGICK => 'Imagine\Imagick\Imagine',
            self::GMAGICK => 'Imagine\Gmagick\Imagine',
        };
    }
}
