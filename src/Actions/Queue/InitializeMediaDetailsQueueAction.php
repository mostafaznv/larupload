<?php

namespace Mostafaznv\Larupload\Actions\Queue;

use Illuminate\Support\Facades\DB;
use Mostafaznv\Larupload\Jobs\ProcessMediaDetails;
use Mostafaznv\Larupload\Larupload;
use Mostafaznv\Larupload\Storage\Attachment;


class InitializeMediaDetailsQueueAction
{
    public function __invoke(Attachment $attachment, int $id, string $class): void
    {
        // save a copy of the original file to extract media details from when storage is remote, then delete it.
        local_copy($attachment);

        $queueId = DB::table(Larupload::DETAILS_QUEUE_TABLE)->insertGetId([
            'record_id'    => $id,
            'record_class' => $class,
            'created_at'   => now(),
        ]);


        ProcessMediaDetails::dispatch($queueId, $id, $attachment->name, $class);
    }
}
