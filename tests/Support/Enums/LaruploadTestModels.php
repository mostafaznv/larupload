<?php

namespace Mostafaznv\Larupload\Test\Support\Enums;

use Mostafaznv\Larupload\Test\Support\Models\LaruploadHeavyTestModel;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadLightTestModel;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadSoftDeleteTestModel;

enum LaruploadTestModels
{
    case HEAVY;
    case LIGHT;
    case SOFT_DELETE;

    public function instance(): LaruploadSoftDeleteTestModel|LaruploadHeavyTestModel|LaruploadLightTestModel
    {
        return match ($this) {
            self::HEAVY       => new LaruploadHeavyTestModel,
            self::LIGHT       => new LaruploadLightTestModel,
            self::SOFT_DELETE => new LaruploadSoftDeleteTestModel,
        };
    }
}
