<?php

namespace Mostafaznv\Larupload\Concerns\Storage\Attachment;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Mostafaznv\Larupload\Enums\LaruploadFileType;
use Mostafaznv\Larupload\Larupload;
use Mostafaznv\Larupload\Actions\FixExceptionNamesAction;

trait StyleAttachment
{
    /**
     * Handle styles
     * resize, crop and generate styles from original file
     *
     * @param string $id
     * @param Model|string $class
     * @param bool $standalone
     */
    protected function handleStyles(string $id, Model|string $model, bool $standalone = false): void
    {
        switch ($this->type) {
            case LaruploadFileType::IMAGE:
                foreach ($this->imageStyles as $name => $style) {
                    $path = $this->getBasePath($id, $name);
                    $saveTo = $path . '/' . FixExceptionNamesAction::make($this->output['name'], $name)->run();

                    Storage::disk($this->disk)->makeDirectory($path);
                    $this->img($this->file)->resize($saveTo, $style);
                }

                break;

            case LaruploadFileType::VIDEO:
                if ($this->ffmpegQueue) {
                    if ($this->driverIsNotLocal()) {
                        $this->uploadOriginalFile($id, $this->localDisk);
                    }

                    if ($model instanceof Model) {
                        $this->initializeFFMpegQueue(
                            $model->id, $model->getMorphClass(), $standalone
                        );
                    }
                    else {
                        $this->initializeFFMpegQueue(
                            $id, $model, $standalone
                        );
                    }
                }
                else {
                    $this->handleVideoStyles($id);
                }

                break;

            case LaruploadFileType::AUDIO:
                if ($this->ffmpegQueue) {
                    if ($this->driverIsNotLocal()) {
                        $this->uploadOriginalFile($id, $this->localDisk);
                    }

                    if ($model instanceof Model) {
                        $this->initializeFFMpegQueue(
                            $model->id, $model->getMorphClass(), $standalone
                        );
                    }
                    else {
                        $this->initializeFFMpegQueue(
                            $id, $model, $standalone
                        );
                    }
                }
                else {
                    $this->handleAudioStyles($id);
                }

                break;
        }
    }

    /**
     * Handle styles for videos
     *
     * @param $id
     */
    protected function handleVideoStyles($id): void
    {
        foreach ($this->videoStyles as $name => $style) {
            $path = $this->getBasePath($id, $name);
            Storage::disk($this->disk)->makeDirectory($path);
            $saveTo = "$path/{$this->output['name']}";

            $this->ffmpeg()->manipulate($style, $saveTo);
        }

        if (count($this->streams)) {
            $fileName = pathinfo($this->output['name'], PATHINFO_FILENAME) . '.m3u8';

            $path = $this->getBasePath($id, Larupload::STREAM_FOLDER);
            Storage::disk($this->disk)->makeDirectory($path);

            $this->ffmpeg()->stream($this->streams, $path, $fileName);
        }
    }

    /**
     * Handle styles for audios
     *
     * @param $id
     */
    protected function handleAudioStyles($id): void
    {
        foreach ($this->audioStyles as $name => $style) {
            $path = $this->getBasePath($id, $name);
            Storage::disk($this->disk)->makeDirectory($path);
            $saveTo = "$path/{$this->output['name']}";

            $this->ffmpeg()->audio($style, $saveTo);
        }
    }

    /**
     * Prepare style path
     * this function will use to prepare full path of given style to generate url/download response
     *
     * @param string $style
     * @return string|null
     */
    protected function prepareStylePath(string $style): ?string
    {
        $staticStyles = [
            Larupload::ORIGINAL_FOLDER,
            Larupload::COVER_FOLDER,
            Larupload::STREAM_FOLDER
        ];

        if (isset($this->id) and (in_array($style, $staticStyles) or array_key_exists($style, $this->imageStyles) or array_key_exists($style, $this->videoStyles) or array_key_exists($style, $this->audioStyles))) {
            $name = $style == Larupload::COVER_FOLDER
                ? $this->output['cover']
                : $this->output['name'];

            $type = $this->output['type']
                ? LaruploadFileType::from($this->output['type'])
                : null;

            if ($name and $style == Larupload::STREAM_FOLDER) {
                if ($type === LaruploadFileType::VIDEO) {
                    $name = pathinfo($name, PATHINFO_FILENAME) . '.m3u8';
                    $path = $this->getBasePath($this->id, $style);

                    return "$path/$name";
                }

                return null;
            }
            else if ($name and $this->styleHasFile($style)) {

                $name = FixExceptionNamesAction::make($name, $style, $this->getStyle($style))->run();
                $path = $this->getBasePath($this->id, $style);

                return "$path/$name";
            }
        }

        return null;
    }
}
