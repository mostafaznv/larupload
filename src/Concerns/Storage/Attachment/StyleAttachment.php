<?php

namespace Mostafaznv\Larupload\Concerns\Storage\Attachment;

use Illuminate\Support\Facades\Storage;
use Mostafaznv\Larupload\Enums\LaruploadFileType;
use Mostafaznv\Larupload\LaruploadEnum;

trait StyleAttachment
{
    /**
     * Handle styles
     * resize, crop and generate styles from original file
     *
     * @param int $id
     * @param string $class
     * @param bool $standalone
     * @throws \Exception
     */
    protected function handleStyles(int $id, string $class, bool $standalone = false): void
    {
        switch ($this->type) {
            case LaruploadFileType::IMAGE:
                foreach ($this->styles as $name => $style) {
                    $styleTypes = enum_to_names($style->type);

                    if (count($style->type) and !in_array(LaruploadFileType::IMAGE->name, $styleTypes)) {
                        continue;
                    }

                    $path = $this->getBasePath($id, $name);
                    $saveTo = $path . '/' . $this->fixExceptionNames($this->output['name'], $name);

                    Storage::disk($this->disk)->makeDirectory($path);
                    $this->image($this->file)->resize($saveTo, $style);
                }

                break;

            case LaruploadFileType::VIDEO:
                if ($this->ffmpegQueue) {
                    $this->initializeFFMpegQueue($id, $class, $standalone);
                }
                else {
                    $this->handleVideoStyles($id);
                }

                break;
        }
    }

    /**
     * Handle styles for videos
     *
     * @param $id
     * @throws \Exception
     */
    protected function handleVideoStyles($id): void
    {
        foreach ($this->styles as $name => $style) {
            $types = enum_to_names($style->type);

            if ((count($types) and !in_array(LaruploadFileType::VIDEO->name, $types))) {
                continue;
            }

            $path = $this->getBasePath($id, $name);
            Storage::disk($this->disk)->makeDirectory($path);
            $saveTo = "$path/{$this->output['name']}";

            $this->ffmpeg()->manipulate($style, $saveTo);
        }

        if (count($this->streams)) {
            $fileName = pathinfo($this->output['name'], PATHINFO_FILENAME) . '.m3u8';

            $path = $this->getBasePath($id, LaruploadEnum::STREAM_FOLDER);
            Storage::disk($this->disk)->makeDirectory($path);

            $this->ffmpeg()->stream($this->streams, $path, $fileName);
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
            LaruploadEnum::ORIGINAL_FOLDER, LaruploadEnum::COVER_FOLDER, LaruploadEnum::STREAM_FOLDER
        ];

        if (isset($this->id) and (in_array($style, $staticStyles) or array_key_exists($style, $this->styles))) {
            $name = $style == LaruploadEnum::COVER_FOLDER
                ? $this->output['cover']
                : $this->output['name'];

            $type = $this->output['type']
                ? LaruploadFileType::from($this->output['type'])
                : null;

            if ($name and $style == LaruploadEnum::STREAM_FOLDER) {
                if ($type === LaruploadFileType::VIDEO) {
                    $name = pathinfo($name, PATHINFO_FILENAME) . '.m3u8';
                    $path = $this->getBasePath($this->id, $style);

                    return "$path/$name";
                }

                return null;
            }
            else if ($name and $this->styleHasFile($style)) {
                $name = $this->fixExceptionNames($name, $style);
                $path = $this->getBasePath($this->id, $style);

                return "$path/$name";
            }
        }

        return null;
    }
}
