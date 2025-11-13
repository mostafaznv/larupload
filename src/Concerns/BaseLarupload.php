<?php

namespace Mostafaznv\Larupload\Concerns;

use Mostafaznv\Larupload\Storage\Attachment;
use Mostafaznv\Larupload\Storage\Proxy\AttachmentProxy;

trait BaseLarupload
{
    private bool $hideLaruploadColumns;

    /**
     * @var Attachment[]
     */
    private array $attachments = [];

    protected function initializeLarupload(): void
    {
        $this->hideLaruploadColumns = config('larupload.hide-table-columns');

        $this->attachments = $this->attachments();
        $table = $this->getTable();

        foreach ($this->attachments as $attachment) {
            $attachment->folder($table);
        }
    }

    /**
     * Get the entities should upload into the model
     *
     * @return Attachment[]
     */
    abstract public function attachments(): array;
}
