<?php

namespace Mostafaznv\Larupload\Concerns\Storage\Attachment;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Mostafaznv\Larupload\Actions\GuessLaruploadFileTypeAction;
use Mostafaznv\Larupload\Enums\LaruploadFileType;
use Mostafaznv\Larupload\Jobs\ProcessFFMpeg;
use Mostafaznv\Larupload\Larupload;


trait QueueAttachment
{
    /**
     * Handle FFMpeg queue on running ffmpeg queue:work
     */
    public function handleFFMpegQueue(bool $isLastOne = false, bool $standalone = false): void
    {
        $this->file = $this->prepareFileForFFMpegProcess();
        $this->type = GuessLaruploadFileTypeAction::make($this->file)->calc();

        if ($this->type == LaruploadFileType::VIDEO) {
            $this->handleVideoStyles($this->id);
        }
        else if ($this->type == LaruploadFileType::AUDIO) {
            $this->handleAudioStyles($this->id);
        }

        if ($this->driverIsNotLocal() and $isLastOne) {
            Storage::disk($this->localDisk)->deleteDirectory(
                $standalone ? "$this->name" : "$this->folder/$this->id"
            );
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
            $availableQueues = DB::table(Larupload::FFMPEG_QUEUE_TABLE)
                ->where('status', 0)
                ->count();

            if ($availableQueues < $maxQueueNum) {
                $flag = true;
            }
        }


        if ($flag) {
            // save a copy of original file to use it on process ffmpeg queue, then delete it
            if ($this->driverIsNotLocal()) {
                $path = $this->getBasePath($id, Larupload::ORIGINAL_FOLDER);
                Storage::disk($this->localDisk)->putFileAs($path, $this->file, $this->output['name']);
            }

            $queueId = DB::table(Larupload::FFMPEG_QUEUE_TABLE)->insertGetId([
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

    private function prepareFileForFFMpegProcess(): UploadedFile
    {
        $basePath = $this->getBasePath($this->id, Larupload::ORIGINAL_FOLDER);
        $path = $basePath . '/' . $this->output['name'];
        $disk = $this->driverIsLocal() ? $this->disk : $this->localDisk;

        $path = Storage::disk($disk)->path($path);

        return new UploadedFile($path, $this->output['name'], null, null, true);
    }
}
