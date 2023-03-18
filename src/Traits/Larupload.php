<?php

namespace Mostafaznv\Larupload\Traits;

use Mostafaznv\Larupload\Concerns\BaseLarupload;
use Mostafaznv\Larupload\Concerns\LaruploadObservers;
use Mostafaznv\Larupload\Concerns\LaruploadRelations;
use Mostafaznv\Larupload\Concerns\LaruploadTransformers;

/**
 * @method static \Illuminate\Database\Eloquent\Relations\HasOne laruploadQueue()
 * @method static \Illuminate\Database\Eloquent\Relations\HasMany laruploadQueues()
 */
trait Larupload
{
    use BaseLarupload;
    use LaruploadObservers;
    use LaruploadRelations;
    use LaruploadTransformers;
}
