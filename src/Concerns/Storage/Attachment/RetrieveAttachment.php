<?php

namespace Mostafaznv\Larupload\Concerns\Storage\Attachment;

use Illuminate\Support\Facades\Storage;
use Mostafaznv\Larupload\Actions\FixExceptionNamesAction;
use Mostafaznv\Larupload\Enums\LaruploadFileType;
use Mostafaznv\Larupload\Larupload;
use stdClass;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;


trait RetrieveAttachment
{
    public function meta(?string $key = null): object|int|string|null
    {
        if ($key) {
            $meta = $this->output;

            if (array_key_exists($key, $meta)) {
                return $meta[$key];
            }

            return null;
        }

        return $this->outputToObject();
    }

    public function urls(): object
    {
        $styles = new stdClass();
        $staticStyles = [Larupload::ORIGINAL_FOLDER, Larupload::COVER_FOLDER, Larupload::STREAM_FOLDER];
        $allStyles = array_merge(
            $staticStyles,
            array_keys($this->imageStyles),
            array_keys($this->videoStyles),
            array_keys($this->audioStyles)
        );

        foreach ($allStyles as $style) {
            if ($style == Larupload::COVER_FOLDER and !$this->generateCover) {
                $styles->{$style} = null;
                continue;
            }
            else if ($style == Larupload::STREAM_FOLDER and empty($this->streams)) {
                unset($styles->{$style});
                continue;
            }

            $styles->{$this->nameStyle($style)} = $this->url($style);
        }

        if ($this->withMeta) {
            $styles->meta = $this->meta();
        }

        return $styles;
    }

    public function url(string $style = Larupload::ORIGINAL_FOLDER): ?string
    {
        if (isset($this->file) and $this->file === false) {
            return null;
        }

        $path = $this->prepareStylePath($style);

        if ($path) {
            if (disk_driver_is_local($this->disk)) {
                $url = Storage::disk($this->disk)->url($path);

                return url($url);
            }

            $baseUrl = config("filesystems.disks.$this->disk.url");

            if ($baseUrl) {
                return "$baseUrl/$path";
            }

            return $path;
        }

        return null;
    }

    public function download(string $style = Larupload::ORIGINAL_FOLDER): StreamedResponse|RedirectResponse|null
    {
        if (isset($this->file) and $this->file === false) {
            return null;
        }

        $path = $this->prepareStylePath($style);

        if ($path) {
            if (disk_driver_is_local($this->disk)) {
                return Storage::disk($this->disk)->download($path);
            }

            $baseUrl = config("filesystems.disks.$this->disk.url");

            if ($baseUrl) {
                return redirect("$baseUrl/$path");
            }

            return null;
        }

        return null;
    }

    private function prepareStylePath(string $style): ?string
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
                    $path = larupload_relative_path($this, $this->id, $style);

                    return "$path/$name";
                }

                return null;
            }
            else if ($name and $this->styleHasFile($style)) {
                $name = FixExceptionNamesAction::make($name, $style, $this->getStyle($style))->run();
                $path = larupload_relative_path($this, $this->id, $style);

                return "$path/$name";
            }
        }

        return null;
    }
}
