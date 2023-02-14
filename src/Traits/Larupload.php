<?php

namespace Mostafaznv\Larupload\Traits;

use Mostafaznv\Larupload\Concerns\BootLarupload;
use Mostafaznv\Larupload\Concerns\LaruploadRelations;
use Mostafaznv\Larupload\Concerns\LaruploadTransformers;
use Mostafaznv\Larupload\Storage\Attachment;

trait Larupload
{
    use BootLarupload;
    use LaruploadRelations;
    use LaruploadTransformers;

    private array $attachments = [];


    /**
     * Get the entities should upload into the model
     *
     * @return Attachment[]
     */
    abstract public function attachments(): array;
}
