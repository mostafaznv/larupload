<?php

namespace Mostafaznv\Larupload\Storage;

use Mostafaznv\Larupload\Helpers\LaraTools;
use Mostafaznv\Larupload\LaruploadEnum;
use Mostafaznv\Larupload\UploadEntities;
use stdClass;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\UploadedFile;
use Mostafaznv\Larupload\Jobs\ProcessFFMpeg;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Attachment extends UploadEntities
{
    use LaraTools;

    /**
     * Attach files into entity
     *
     * @param mixed $file
     * @param UploadedFile|null $cover
     * @return bool
     */
    public function attach($file, $cover = null): bool
    {
        if (($this->fileIsSetAndHasValue($file) or $file == LARUPLOAD_NULL) and ($this->fileIsSetAndHasValue($cover) or $cover == null)) {
            $this->file = $file;

            if ($file != LARUPLOAD_NULL) {
                $this->cover = $cover;
                $this->type = $this->getFileType($file);
            }

            return true;
        }

        return false;
    }

    /**
     * After save event to process all uploads, converts and ...
     *
     * @param Model $model
     * @return Model
     * @throws Exception
     */
    public function saved(Model $model): Model
    {
        if (isset($this->file)) {
            if ($this->file == LARUPLOAD_NULL) {
                $this->clean($model->id);
            }
            else {
                if (!$this->keepOldFiles) {
                    $this->clean($model->id);
                }

                $this->setBasicDetails();
                $this->setMediaDetails();
                $this->uploadOriginalFile($model->id);
                $this->setCover($model->id);
                $this->handleStyles($model->id, $model->getMorphClass());
            }

            $model = $this->setAttributes($model);
        }

        return $model;
    }

    /**
     * After delete event to delete files
     *
     * @param Model $model
     */
    public function deleted(Model $model): void
    {
        if (!$this->preserveFiles) {
            Storage::disk($this->disk)->deleteDirectory("{$this->folder}/{$model->id}");
        }
    }

    /**
     * Generate URL for attached file
     * Remember, if you are using the local driver, all files that should be publicly accessible should be placed in the storage/app/public directory. Furthermore, you should create a symbolic link at public/storage which points to the  storage/app/public directory
     *
     * @param string $style
     * @return null|string
     */
    public function url(string $style = LaruploadEnum::ORIGINAL_FOLDER): ?string
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
    public function download(string $style = 'original')
    {
        $path = $this->prepareStylePath($style);

        if ($path) {
            return $this->storageDownload($path);
        }

        return null;
    }

    /**
     * Get meta data as an array or object
     *
     * @param string|null $key
     * @return object|string|integer|null
     */
    public function meta(string $key = null)
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
        $staticStyles = [LaruploadEnum::ORIGINAL_FOLDER, LaruploadEnum::COVER_FOLDER, LaruploadEnum::STREAM_FOLDER];
        $allStyles = array_merge($staticStyles, array_keys($this->styles));
        $styles = new stdClass();

        foreach ($allStyles as $style) {
            if ($style == LaruploadEnum::COVER_FOLDER and $this->generateCover == false) {
                $styles->{$style} = null;
                continue;
            }
            else if ($style == LaruploadEnum::STREAM_FOLDER and empty($this->streams)) {
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
     * Handle FFMpeg queue on running ffmpeg queue:work
     *
     * @throws Exception
     */
    public function handleFFMpegQueue(): void
    {
        $path = $this->getBasePath($this->id, LaruploadEnum::ORIGINAL_FOLDER);
        $path = Storage::disk($this->disk)->path("$path/{$this->output['name']}");
        $this->file = new UploadedFile($path, $this->output['name'], null, null, true);
        $this->type = $this->getFileType($this->file);

        $this->handleVideoStyles($this->id);

        if ($this->driverIsNotLocal()) {
            $localPath = $this->getBasePath($this->id, '');
            Storage::disk($this->localDisk)->deleteDirectory($localPath);
        }
    }

    /**
     * Set some basic details
     */
    protected function setBasicDetails(): void
    {
        $this->output['name'] = $this->setFileName();
        $this->output['format'] = $this->file->getClientOriginalExtension();
        $this->output['size'] = $this->file->getSize();
        $this->output['type'] = $this->type;
        $this->output['mime_type'] = $this->file->getMimeType();
    }

    /**
     * Set media details
     *
     * @throws Exception
     */
    protected function setMediaDetails(): void
    {
        switch ($this->type) {
            case LaruploadEnum::VIDEO:
            case LaruploadEnum::AUDIO:
                $meta = $this->ffmpeg()->getMeta();

                $this->output['width'] = $meta['width'];
                $this->output['height'] = $meta['height'];
                $this->output['duration'] = $meta['duration'];

                break;

            case LaruploadEnum::IMAGE:
                $meta = $this->image()->getMeta();

                $this->output['width'] = $meta['width'];
                $this->output['height'] = $meta['height'];
                $this->output['dominant_color'] = $this->dominantColor ? $this->image()->getDominantColor($this->file) : null;

                break;
        }
    }

    /**
     * Upload original file
     *
     * @param int $id
     */
    protected function uploadOriginalFile(int $id)
    {
        $path = $this->getBasePath($id, LaruploadEnum::ORIGINAL_FOLDER);
        Storage::disk($this->disk)->putFileAs($path, $this->file, $this->output['name']);
    }

    /**
     * Set cover photo
     * Generate cover photo automatically from photos and videos, if cover file was null
     *
     * @param $id
     * @throws Exception
     */
    protected function setCover($id): void
    {
        $path = $this->getBasePath($id, LaruploadEnum::COVER_FOLDER);
        $fileName = pathinfo($this->output['name'], PATHINFO_FILENAME);
        $format = $this->type == LaruploadEnum::IMAGE ? ($this->output['format'] == 'svg' ? 'png' : $this->output['format']) : 'jpg';
        $name = "{$fileName}.{$format}";

        // in case cover uploaded by user
        if ($this->fileIsSetAndHasValue($this->cover) and ($this->mimeToType($this->cover->getMimeType()) == LaruploadEnum::IMAGE)) {
            Storage::disk($this->disk)->putFileAs($path, $this->cover, $name);

            $this->output['cover'] = $name;
        }
        else {
            if (!$this->generateCover) {
                return;
            }

            $saveTo = "{$path}/{$name}";

            switch ($this->type) {
                case LaruploadEnum::VIDEO:
                    Storage::disk($this->disk)->makeDirectory($path);

                    $this->ffmpeg()->capture($this->ffmpegCaptureFrame, $this->coverStyle, $saveTo);

                    $cover = Storage::disk($this->disk)->path($saveTo);
                    $cover = new UploadedFile($cover, $name);

                    $this->output['cover'] = $name;
                    $this->output['dominant_color'] = $this->dominantColor ? $this->image($cover)->getDominantColor() : null;

                    break;

                case LaruploadEnum::IMAGE:
                    Storage::disk($this->disk)->makeDirectory($path);

                    $result = $this->image()->resize($saveTo, $this->coverStyle);

                    if ($result) {
                        $this->output['cover'] = $name;
                    }

                    break;
            }
        }
    }

    /**
     * Handle styles
     * resize, crop and generate styles from original file
     *
     * @param int $id
     * @param string $class
     * @param bool $standalone
     * @throws Exception
     */
    protected function handleStyles(int $id, string $class, bool $standalone = false): void
    {
        switch ($this->type) {
            case LaruploadEnum::IMAGE:
                foreach ($this->styles as $name => $style) {
                    if (count($style['type']) and !in_array(LaruploadEnum::IMAGE, $style['type'])) {
                        continue;
                    }

                    $path = $this->getBasePath($id, $name);
                    $saveTo = $path . '/' . $this->fixExceptionNames($this->output['name'], $name);

                    Storage::disk($this->disk)->makeDirectory($path);
                    $this->image()->resize($saveTo, $style);
                }

                break;

            case 'video':
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
     * @throws Exception
     */
    protected function handleVideoStyles($id): void
    {
        foreach ($this->styles as $name => $style) {
            if ((count($style['type']) and !in_array(LaruploadEnum::VIDEO, $style['type']))) {
                continue;
            }

            $path = $this->getBasePath($id, $name);
            Storage::disk($this->disk)->makeDirectory($path);
            $saveTo = "{$path}/{$this->output['name']}";

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
     * Initialize FFMPEG Queue
     *
     * @param int $id
     * @param string $class
     * @param bool $standalone
     */
    protected function initializeFFMpegQueue(int $id, string $class, bool $standalone = false)
    {
        $maxQueueNum = $this->ffmpegMaxQueueNum;
        $flag = false;

        if ($maxQueueNum == 0) {
            $flag = true;
        }
        else {
            $availableQueues = DB::table('larupload_ffmpeg_queue')->where('status', 0)->count();

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

            $statusId = DB::table('larupload_ffmpeg_queue')->insertGetId([
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


            ProcessFFMpeg::dispatch($statusId, $id, $this->name, $class, $serializedClass)->delay(now()->addSeconds(1));
        }
        else {
            throw new HttpResponseException(redirect(URL::previous())->withErrors([
                'ffmpeg_queue_max_num' => trans('larupload::messages.max-queue-num-exceeded')
            ]));
        }
    }

    /**
     * Clean directory before upload
     *
     * @param $id
     */
    protected function clean($id): void
    {
        $path = $this->getBasePath($id);
        Storage::disk($this->disk)->deleteDirectory($path);

        foreach ($this->output as $key => $value) {
            $this->output[$key] = null;
        }
    }

    /**
     * Set attributes before saving event
     *
     * @param Model $model
     * @return Model
     */
    protected function setAttributes(Model $model): Model
    {
        if ($this->mode == 'heavy') {
            foreach ($this->output as $key => $value) {
                $model->{"{$this->name}_file_$key"} = $value;
            }
        }
        else {
            $model->{"{$this->name}_file_name"} = $this->output['name'] ?? null;
            $model->{"{$this->name}_file_meta"} = json_encode($this->output);
        }

        return $model;
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
        $staticStyles = [LaruploadEnum::ORIGINAL_FOLDER, LaruploadEnum::COVER_FOLDER, LaruploadEnum::STREAM_FOLDER];

        if ($this->id and (in_array($style, $staticStyles) or array_key_exists($style, $this->styles))) {
            $name = $style == LaruploadEnum::COVER_FOLDER ? $this->output['cover'] : $this->output['name'];
            $type = $this->output['type'];

            if ($name and $style == LaruploadEnum::STREAM_FOLDER) {
                if ($type == LaruploadEnum::VIDEO) {
                    $name = pathinfo($name, PATHINFO_FILENAME) . '.m3u8';
                    $path = $this->getBasePath($this->id, $style);
                    $path = "$path/$name";

                    return $path;
                }

                return null;
            }
            else if ($name and $this->styleHasFile($style)) {
                $name = $this->fixExceptionNames($name, $style);
                $path = $this->getBasePath($this->id, $style);
                $path = "$path/$name";

                return $path;
            }
        }

        return null;
    }
}
