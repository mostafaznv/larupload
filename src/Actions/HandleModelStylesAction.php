<?php

namespace Mostafaznv\Larupload\Actions;

use Illuminate\Database\Eloquent\Model;
use Mostafaznv\Larupload\Exceptions\FFMpegQueueMaxNumExceededException;
use Mostafaznv\Larupload\Storage\Attachment;


class HandleModelStylesAction
{
    /**
     * @param Model $model
     * @param Attachment[] $attachments
     *
     * @throws FFMpegQueueMaxNumExceededException
     */
    public function __invoke(Model $model, array $attachments): void
    {
        foreach ($attachments as $attachment) {
            if ($attachment->shouldProcessStyles()) {
                HandleStylesAction::make($attachment)->run($attachment->id, $model);
            }
        }
    }
}
