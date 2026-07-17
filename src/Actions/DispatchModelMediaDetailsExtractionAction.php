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
        $extractOnQueue = config('larupload.extract-media-details-on-queue', false);

        if ($extractOnQueue === false) {
            return;
        }

        foreach ($attachments as $attachment) {
            resolve(InitializeMediaDetailsQueueAction::class)(
                $attachment, $model->id, $model->getMorphClass()
            );
        }
    }
}
