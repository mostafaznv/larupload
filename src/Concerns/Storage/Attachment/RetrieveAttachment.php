<?php

namespace Mostafaznv\Larupload\Concerns\Storage\Attachment;


use Mostafaznv\Larupload\Larupload;
use stdClass;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

trait RetrieveAttachment
{
    /**
     * Get metadata as an array or object
     *
     * @param string|null $key
     * @return object|string|integer|null
     */
    public function meta(string $key = null): object|int|string|null
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

    /**
     * Get url for all styles (original, cover and ...) of current entity
     *
     * @return object
     */
    public function urls(): object
    {
        $styles = new stdClass();
        $staticStyles = [Larupload::ORIGINAL_FOLDER, Larupload::COVER_FOLDER, Larupload::STREAM_FOLDER];
        $allStyles = array_merge(
            $staticStyles,
            array_keys($this->imageStyles),
            array_keys($this->videoStyles)
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

    /**
     * Generate URL for attached file
     * Remember, if you are using the local driver, all files that should be publicly accessible should be placed in the storage/app/public directory. Furthermore, you should create a symbolic link at public/storage which points to the  storage/app/public directory
     *
     * @param string $style
     * @return null|string
     */
    public function url(string $style = Larupload::ORIGINAL_FOLDER): ?string
    {
        $path = $this->prepareStylePath($style);

        if ($path) {
            return $this->storageUrl($path);
        }

        return null;
    }

    /**
     * Download attached file
     *
     * @param string $style
     * @return RedirectResponse|StreamedResponse|null
     */
    public function download(string $style = Larupload::ORIGINAL_FOLDER): StreamedResponse|RedirectResponse|null
    {
        $path = $this->prepareStylePath($style);

        if ($path) {
            return $this->storageDownload($path);
        }

        return null;
    }
}
