<?php

namespace Mostafaznv\Larupload\Enums;

enum LaruploadSecureIdsMethod
{
    case ULID;
    case UUID;
    case HASHID;
    case NONE;
}
