<?php

namespace Mostafaznv\Larupload\Test\Support\Enums;

use Mostafaznv\Larupload\Test\Support\Models\LaruploadHeavyTestModel;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadLightTestModel;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadQueueTestModel;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadRemoteQueueTestModel;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadSoftDeleteTestModel;

enum LaruploadTestModels
{
    case HEAVY;
    case LIGHT;
    case QUEUE;
    case REMOTE_QUEUE;
    case SOFT_DELETE;

    public function instance(): LaruploadSoftDeleteTestModel|LaruploadHeavyTestModel|LaruploadLightTestModel|LaruploadQueueTestModel|LaruploadRemoteQueueTestModel
    {
        return match ($this) {
            self::HEAVY        => new LaruploadHeavyTestModel,
            self::LIGHT        => new LaruploadLightTestModel,
            self::QUEUE        => new LaruploadQueueTestModel,
            self::REMOTE_QUEUE => new LaruploadRemoteQueueTestModel,
            self::SOFT_DELETE  => new LaruploadSoftDeleteTestModel,
        };
    }
}
