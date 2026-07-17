<?php

namespace Mostafaznv\Larupload\Actions;

use Illuminate\Database\Eloquent\Model;
use Mostafaznv\Larupload\Actions\Queue\InitializeMediaDetailsQueueAction;
use Mostafaznv\Larupload\Storage\Attachment;


class DispatchModelMediaDetailsExtractionAction
{
    /**
     * @param Model $model
     * @param Attachment[] $attachments
     */
    public function __invoke(Model $model, array $attachments): void
    {
        $fetchOnQueue = config('larupload.fetch-media-details-on-queue', false);

        if ($fetchOnQueue === false) {
            return;
        }

        foreach ($attachments as $attachment) {
            resolve(InitializeMediaDetailsQueueAction::class)(
                $attachment, $model->id, $model->getMorphClass()
            );
        }
    }
}
