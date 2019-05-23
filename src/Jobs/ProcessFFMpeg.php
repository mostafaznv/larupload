<?php

namespace Mostafaznv\Larupload\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Exception;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Mostafaznv\Larupload\Events\LaruploadFFMpegQueueFinished;
use Mostafaznv\Larupload\Storage\Attachment;

class ProcessFFMpeg implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $statusId;
    protected $id;
    protected $name;
    protected $model;
    protected $folder;
    protected $options;
    protected $meta;


    public function __construct($statusId, $id, $name, $model, $folder, $options, $meta)
    {
        $this->statusId = $statusId;
        $this->id = $id;
        $this->name = $name;
        $this->model = $model;
        $this->folder = $folder;
        $this->options = $options;
        $this->meta = $meta;
    }

    public function handle()
    {
        $this->updateStatus(['started_at' => now()]);

        try {
            $attachment = new Attachment($this->name, $this->folder, $this->options);
            $attachment->handleFFMpegQueue($this->id, $this->meta);
        }
        catch (FileNotFoundException $e) {
            $this->updateStatus(['finished_at' => now()]);
        }
        catch (Exception $e) {
            $this->updateStatus(['finished_at' => now()]);
        }

        $this->updateStatus(['status' => 1, 'finished_at' => now()]);
    }

    /**
     * Update LaruploadFFMpegQueue table
     *
     * @param $data
     * @return int
     */
    protected function updateStatus($data)
    {
        $result = DB::table('larupload_ffmpeg_queue')->where('id', $this->statusId)->update($data);

        if ($result and isset($data['status']) and $data['status']) {
            event(new LaruploadFFMpegQueueFinished($this->id, $this->model, $this->statusId));
        }

        return $result;
    }
}
