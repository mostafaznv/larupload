<?php

namespace Mostafaznv\Larupload\Enums;

enum LaruploadFileType
{
    case IMAGE;
    case VIDEO;
    case AUDIO;
    case PDF;
    case COMPRESSED;
    case FILE;

    public static function from(string $type): LaruploadFileType
    {
        return match ($type) {
            'IMAGE'      => LaruploadFileType::IMAGE,
            'VIDEO'      => LaruploadFileType::VIDEO,
            'AUDIO'      => LaruploadFileType::AUDIO,
            'PDF'        => LaruploadFileType::PDF,
            'COMPRESSED' => LaruploadFileType::COMPRESSED,
            default      => LaruploadFileType::FILE,
        };
    }
}
