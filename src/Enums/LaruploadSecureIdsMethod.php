<?php

namespace Mostafaznv\Larupload\Enums;


enum LaruploadSecureIdsMethod
{
    case ULID;
    case UUID;
    case SQID;
    case HASHID;
    case NONE;


    public function hasUnifiedAttachments(): bool
    {
        return in_array($this, [
            self::SQID, self::HASHID, self::NONE
        ]);
    }
}
