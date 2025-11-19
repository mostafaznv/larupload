<?php

namespace Mostafaznv\Larupload\Enums;


enum LaruploadSecureIdsMethod
{
    case ULID;
    case UUID;
    case SQID;
    case HASHID;
    case NONE;
}
