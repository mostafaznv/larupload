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

class ProcessFFMpeg implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int    $statusId;
    protected int    $id;
    protected string $name;
    protected string $model;


    public function __construct(int $statusId, int $id, string $name, string $model)
    {
        $this->statusId = $statusId;
        $this->id = $id;
        $this->name = $name;
        $this->model = $model;
    }

    public function handle()
    {
        $this->updateStatus(false, true);

        try {
            $class = $this->model;
            $model = $class::where('id', $this->id)->first();

            $model->{$this->name}->handleFFMpegQueue();

            $this->updateStatus(true, false);
        }
        catch (FileNotFoundException | Exception $e) {
            $this->updateStatus(false, false, $e->getMessage());
        }
    }

    /**
     * Update LaruploadFFMpegQueue table
     *
     * @param bool $status
     * @param bool $isStarted
     * @param string|null $message
     * @return int
     */
    protected function updateStatus(bool $status, bool $isStarted, string $message = null): int
    {
        $dateColumn = $isStarted ? 'started_at' : 'finished_at';

        $result = DB::table('larupload_ffmpeg_queue')->where('id', $this->statusId)->update([
            'status'    => $status,
            'message'   => $message,
            $dateColumn => now(),
        ]);

        if ($result and $status) {
            event(new LaruploadFFMpegQueueFinished($this->id, $this->model, $this->statusId));
        }

        return $result;
    }
}
