<?php

namespace Mostafaznv\Larupload\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Mostafaznv\Larupload\Events\LaruploadMediaDetailsQueueFinished;
use Mostafaznv\Larupload\Larupload;
use Mostafaznv\Larupload\Storage\Proxy\AttachmentProxy;


class ProcessMediaDetails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int    $queueId;
    protected int    $id;
    protected string $name;
    protected string $model;


    public function __construct(int $queueId, int $id, string $name, string $model)
    {
        $this->queueId = $queueId;
        $this->id = $id;
        $this->name = $name;
        $this->model = $model;


    }

    /**
     * @throws Exception
     */
    public function handle(): void
    {
        $this->updateStatus(false, true);


        try {
            /** @var Model $class */
            $class = class_exists($this->model) ? $this->model : Relation::getMorphedModel($this->model);
            $model = $class::query()->where('id', $this->id)->first();

            /** @var AttachmentProxy|null $attachment */
            $attachment = $model?->{$this->name} ?? null;

            if ($attachment) {
                $attachment->handleMediaDetailsQueue($model);
            }
            else {
                throw new FileNotFoundException('Unable to process media details: the model or attached file could not be found.');
            }

            $this->updateStatus(true, false);
        }
        catch (Exception $e) {
            $this->updateStatus(false, false, $e->getMessage());

            throw new Exception($e->getMessage());
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Update DETAILS_QUEUE_TABLE table
     *
     * @param bool $status
     * @param bool $isStarted
     * @param string|null $message
     * @return int
     */
    protected function updateStatus(bool $status, bool $isStarted, ?string $message = null): int
    {
        $dateColumn = $isStarted ? 'started_at' : 'finished_at';

        $result = DB::table(Larupload::DETAILS_QUEUE_TABLE)->where('id', $this->queueId)->update([
            'status'    => $status,
            'message'   => $message,
            $dateColumn => now(),
        ]);

        if ($result and $status) {
            event(new LaruploadMediaDetailsQueueFinished($this->id, $this->model, $this->queueId));
        }

        return $result;
    }
}
