<?php

namespace Mostafaznv\Larupload\Actions\Queue;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Mostafaznv\Larupload\Exceptions\FFMpegQueueMaxNumExceededException;
use Mostafaznv\Larupload\Jobs\ProcessFFMpeg;
use Mostafaznv\Larupload\Larupload;
use Mostafaznv\Larupload\Storage\Attachment;


class InitializeFFMpegQueueAction
{
    public function __invoke(Attachment $attachment, int $id, string $class, bool $standalone = false): void
    {
        $flag = false;
        $maxQueueNum = $attachment->ffmpegMaxQueueNum;


        if ($maxQueueNum == 0) {
            $flag = true;
        }
        else {
            $availableQueues = DB::table(Larupload::FFMPEG_QUEUE_TABLE)
                ->where('status', 0)
                ->count();

            if ($availableQueues < $maxQueueNum) {
                $flag = true;
            }
        }


        if ($flag) {
            // save a copy of the original file to use it on process ffmpeg queue, then delete it
            if (!disk_driver_is_local($attachment->disk)) {
                $path = larupload_relative_path($attachment, $id, Larupload::ORIGINAL_FOLDER);

                Storage::disk($attachment->localDisk)->putFileAs($path, $attachment->file, $attachment->output->name);
            }

            $queueId = DB::table(Larupload::FFMPEG_QUEUE_TABLE)->insertGetId([
                'record_id'    => $id,
                'record_class' => $class,
                'created_at'   => now(),
            ]);

            $serializedClass = null;

            if ($standalone) {
                unset($attachment->file);
                unset($attachment->cover);
                unset($attachment->image);
                unset($attachment->ffmpeg);

                $serializedClass = base64_encode(serialize($attachment));
            }


            ProcessFFMpeg::dispatch($queueId, $id, $attachment->name, $class, $serializedClass);
        }
        else {
            throw new FFMpegQueueMaxNumExceededException;
        }
    }
}
