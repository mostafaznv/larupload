<?php

namespace Mostafaznv\Larupload\Concerns\Storage\Attachment;


use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Mostafaznv\Larupload\Jobs\ProcessFFMpeg;
use Mostafaznv\Larupload\LaruploadEnum;

trait QueueAttachment
{
    /**
     * Handle FFMpeg queue on running ffmpeg queue:work
     */
    public function handleFFMpegQueue(bool $isLastOne = false): void
    {
        $driverIsLocal = $this->driverIsLocal();

        $path = $this->getBasePath($this->id, LaruploadEnum::ORIGINAL_FOLDER);
        $path = Storage::disk($driverIsLocal ? $this->disk : $this->localDisk)->path("$path/{$this->output['name']}");
        $this->file = new UploadedFile($path, $this->output['name'], null, null, true);
        $this->type = $this->getFileType($this->file);

        $this->handleVideoStyles($this->id);

        if (!$driverIsLocal and $isLastOne) {
            Storage::disk($this->localDisk)->deleteDirectory("$this->folder/$this->id");
        }
    }

    protected function initializeFFMpegQueue(int $id, string $class, bool $standalone = false): void
    {
        $maxQueueNum = $this->ffmpegMaxQueueNum;
        $flag = false;

        if ($maxQueueNum == 0) {
            $flag = true;
        }
        else {
            $availableQueues = DB::table(LaruploadEnum::FFMPEG_QUEUE_TABLE)->where('status', 0)->count();

            if ($availableQueues < $maxQueueNum) {
                $flag = true;
            }
        }


        if ($flag) {
            // save a copy of original file to use it on process ffmpeg queue, then delete it
            if ($this->driverIsNotLocal()) {
                $path = $this->getBasePath($id, LaruploadEnum::ORIGINAL_FOLDER);
                Storage::disk($this->localDisk)->putFileAs($path, $this->file, $this->output['name']);
            }

            $queueId = DB::table(LaruploadEnum::FFMPEG_QUEUE_TABLE)->insertGetId([
                'record_id'    => $id,
                'record_class' => $class,
                'created_at'   => now(),
            ]);

            $serializedClass = null;
            if ($standalone) {
                unset($this->file);
                unset($this->cover);
                unset($this->image);
                unset($this->ffmpeg);

                $serializedClass = base64_encode(serialize($this));
            }


            ProcessFFMpeg::dispatch($queueId, $id, $this->name, $class, $serializedClass);
        }
        else {
            throw new HttpResponseException(redirect(URL::previous())->withErrors([
                'ffmpeg_queue_max_num' => trans('larupload::messages.max-queue-num-exceeded')
            ]));
        }
    }
}
