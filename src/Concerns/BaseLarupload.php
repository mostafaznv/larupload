<?php

namespace Mostafaznv\Larupload\Concerns;

use Mostafaznv\Larupload\Storage\Attachment;


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
     * Get attachment definitions for this model.
     *
     * Each item must be an instance of Attachment and describes an uploadable
     * field handled by the model.
     *
     * @return Attachment[]
     */
    abstract public function attachments(): array;
}
