<?php

namespace Mostafaznv\Larupload\Enums;

enum LaruploadFileType
{
    case IMAGE;
    case VIDEO;
    case AUDIO;
    case DOCUMENT;
    case COMPRESSED;
    case FILE;

    public static function from(string $type): LaruploadFileType
    {
        return match ($type) {
            'IMAGE'      => LaruploadFileType::IMAGE,
            'VIDEO'      => LaruploadFileType::VIDEO,
            'AUDIO'      => LaruploadFileType::AUDIO,
            'DOCUMENT'   => LaruploadFileType::DOCUMENT,
            'COMPRESSED' => LaruploadFileType::COMPRESSED,
            default      => LaruploadFileType::FILE,
        };
    }
}
