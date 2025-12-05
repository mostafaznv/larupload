<?php

namespace Mostafaznv\Larupload\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Mostafaznv\Larupload\Actions\Queue\InitializeFFMpegQueueAction;
use Mostafaznv\Larupload\DTOs\Style\AudioStyle;
use Mostafaznv\Larupload\Enums\LaruploadFileType;
use Mostafaznv\Larupload\Exceptions\FFMpegQueueMaxNumExceededException;
use Mostafaznv\Larupload\Larupload;
use Mostafaznv\Larupload\Storage\Attachment;
use Mostafaznv\Larupload\Traits\HasImage;
use Mostafaznv\Larupload\Traits\HasVideo;


class HandleStylesAction
{
    use HasImage, HasVideo;


    public function __construct(protected Attachment $attachment) {}

    public static function make(Attachment $attachment): static
    {
        return new static($attachment);
    }


    /**
     * @throws FFMpegQueueMaxNumExceededException
     */
    public function run(string|int $id, Model|string $model, bool $standalone = false): void
    {
        switch ($this->attachment->type) {
            case LaruploadFileType::IMAGE:
                foreach ($this->attachment->imageStyles as $name => $style) {
                    $path = larupload_relative_path($this->attachment, $id, $name);
                    $saveTo = $path . '/' . FixExceptionNamesAction::make($this->attachment->output->name, $name)->run();

                    Storage::disk($this->attachment->disk)->makeDirectory($path);
                    $this->img($this->attachment->file)->resize($saveTo, $style);
                }

                break;

            case LaruploadFileType::VIDEO:
                if ($this->attachment->ffmpegQueue) {
                    /*if ($this->driverIsNotLocal()) {
                        $this->uploadOriginalFile($id, $this->attachment->localDisk);
                    }*/

                    if ($model instanceof Model) {
                        resolve(InitializeFFMpegQueueAction::class)(
                            $this->attachment, $model->id, $model->getMorphClass(), $standalone
                        );
                    }
                    else {
                        resolve(InitializeFFMpegQueueAction::class)(
                            $this->attachment, $id, $model, $standalone
                        );
                    }
                }
                else {
                    $this->handleVideoStyles($id);
                }

                break;

            case LaruploadFileType::AUDIO:
                if ($this->attachment->ffmpegQueue) {
                    /*if ($this->driverIsNotLocal()) {
                        $this->uploadOriginalFile($id, $this->attachment->localDisk);
                    }*/

                    if ($model instanceof Model) {
                        resolve(InitializeFFMpegQueueAction::class)(
                            $this->attachment, $model->id, $model->getMorphClass(), $standalone
                        );
                    }
                    else {
                        resolve(InitializeFFMpegQueueAction::class)(
                            $this->attachment, $id, $model, $standalone
                        );
                    }
                }
                else {
                    $this->handleAudioStyles($id);
                }

                break;
        }
    }

    public function handleVideoStyles(string|int $id): void
    {
        foreach ($this->attachment->videoStyles as $name => $style) {
            $path = larupload_relative_path($this->attachment, $id, $name);
            Storage::disk($this->attachment->disk)->makeDirectory($path);

            $saveTo = "$path/{$this->attachment->output->name}";


            if ($style->isAudioFormat()) {
                $this->ffmpeg($style)->audio(
                    style: AudioStyle::make($style->name, $style->format),
                    saveTo: $saveTo
                );

                continue;
            }

            $this->ffmpeg($style)->manipulate($style, $saveTo);
        }

        if (count($this->attachment->streams)) {
            $fileName = pathinfo($this->attachment->output->name, PATHINFO_FILENAME) . '.m3u8';

            $path = larupload_relative_path($this->attachment, $id, Larupload::STREAM_FOLDER);
            Storage::disk($this->attachment->disk)->makeDirectory($path);

            $this->ffmpeg()->stream($this->attachment->streams, $path, $fileName);
        }
    }

    public function handleAudioStyles(string|int $id): void
    {
        foreach ($this->attachment->audioStyles as $name => $style) {
            $path = larupload_relative_path($this->attachment, $id, $name);
            Storage::disk($this->attachment->disk)->makeDirectory($path);
            $saveTo = "$path/{$this->attachment->output->name}";

            $this->ffmpeg()->audio($style, $saveTo);
        }
    }

    /*protected function uploadOriginalFile(string $id, ?string $disk = null): void
    {
        Storage::disk($disk ?: $this->attachment->disk)
            ->putFileAs(
                path: larupload_relative_path($this->attachment, $id, Larupload::ORIGINAL_FOLDER),
                file: $this->attachment->file,
                name: $this->attachment->output->name
            );
    }*/

    protected function driverIsLocal(): bool
    {
        return disk_driver_is_local($this->attachment->disk);
    }

    protected function driverIsNotLocal(): bool
    {
        return !$this->driverIsLocal();
    }
}
