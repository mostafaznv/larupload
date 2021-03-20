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
     * Set uploaded file
     *
     * @param mixed $file
     * @param UploadedFile|null $cover
     * @return bool
     */
    public function setUploadedFile($file, $cover = null): bool
    {
        // todo - accept urls and convert them to file object (checking possibility to handle this feature with queue)

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

                if ($this->mode == LaruploadEnum::LIGHT_MODE) {
                    foreach ($this->output as $key => $value) {
                        $this->output[$key] = null;
                    }
                }
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
            $path = $this->path . '/' . $model->id;
            Storage::disk($this->disk)->deleteDirectory($path);
        }
    }

    /**
     * Generate URL for attached file
     * Remember, if you are using the local driver, all files that should be publicly accessible should be placed in the storage/app/public directory. Furthermore, you should create a symbolic link at public/storage which points to the  storage/app/public directory
     *
     * @param Model $model
     * @param string $style
     * @return null|string
     */
    public function url(Model $model, string $style = 'original')
    {
        if (in_array($style, ['original', 'cover']) or array_key_exists($style, $this->styles)) {
            list('name' => $name, 'type' => $type) = $this->getTypeAndName($model, $style);

            if ($name and $style == 'stream') {
                if ($type == 'video') {
                    $name = pathinfo($name, PATHINFO_FILENAME) . '.m3u8';
                    $path = $this->getPath($model->id, $style);
                    $path = "$path/$name";

                    return $this->storageUrl($path);
                }
                else {
                    return null;
                }

            }
            else if ($name and $this->hasFile($model, $style)) {
                $name = $this->fixExceptionNames($name, $style);
                $path = $this->getPath($model->id, $style);
                $path = "$path/$name";

                return $this->storageUrl($path);
            }
        }

        return null;
    }

    /**
     * Download attached file
     *
     * @param Model $model
     * @param string $style
     * @return null|string
     */
    public function download(Model $model, string $style = 'original')
    {
        if (in_array($style, ['original', 'cover']) or array_key_exists($style, $this->styles)) {
            list('name' => $name, 'type' => $type) = $this->getTypeAndName($model, $style);

            if ($name and $style == 'stream') {
                if ($type == 'video') {
                    $name = pathinfo($name, PATHINFO_FILENAME) . '.m3u8';
                    $path = $this->getPath($model->id, $style);
                    $path = "$path/$name";

                    return $this->storageDownload($path);
                }
                else {
                    return null;
                }

            }
            else if ($name and $this->hasFile($model, $style)) {
                $name = $this->fixExceptionNames($name, $style);
                $path = $this->getPath($model->id, $style);
                $path = "$path/$name";

                return $this->storageDownload($path);
            }
        }

        return null;
    }

    /**
     * Get All styles (original, cover and ...) for attached field
     *
     * @param Model $model
     * @return object
     */
    public function getFiles(Model $model): object
    {
        $styleNames = array_merge(['original', 'cover'], array_keys($this->styles));
        $styles = new stdClass();

        foreach ($styleNames as $style) {
            if ($style == 'cover' and $this->generateCover == false) {
                $styles->{$style} = null;
                continue;
            }

            $styles->{$style} = $this->url($model, $style);
        }

        if ($this->withMeta) {
            $styles->meta = $this->getMeta($model);
        }

        return $styles;
    }

    /**
     * Get meta data as an array or object
     *
     * @param Model $model
     * @param string $key
     * @return object|string|integer|null
     */
    public function getMeta(Model $model, string $key = null)
    {
        if ($this->mode == 'heavy') {
            $meta = (object)$this->output;

            if ($key) {
                if (property_exists($meta, $key)) {
                    return $model->{"{$this->name}_file_$key"};
                }

                return null;
            }
            else {
                foreach ($meta as $index => $item) {
                    if ($this->file == LARUPLOAD_NULL) {
                        $meta->{$index} = null;
                    }
                    else {
                        $meta->{$index} = $model->{"{$this->name}_file_$index"};
                    }
                }

                return $meta;
            }
        }
        else {
            $meta = json_decode($model->{"{$this->name}_file_meta"});

            if ($key) {
                return property_exists($meta, $key) ? $meta->{$key} : null;
            }

            return $meta;
        }
    }

    /**
     * Handle FFMpeg queue on running ffmpeg queue:work
     *
     * @param $id
     * @param array $meta
     * @throws Exception
     */
    public function handleFFMpegQueue($id, array $meta): void
    {
        $shouldDeletePath = null;
        $path = $this->getPath($id, 'original');

        if ($this->disk == 'local') {
            $path = Storage::disk($this->disk)->path("$path/{$meta['name']}");
        }
        else {
            $shouldDeletePath = $this->getPath($id, '');

            $path = Storage::disk('local')->path("$path/{$meta['name']}");
        }

        $file = new UploadedFile($path, $meta['name'], $meta['mime_type']);
        $this->file = $file;

        $this->type = $this->getFileType($file);
        $this->setBasicDetails();

        $this->handleVideoStyles($id, $file);

        if ($shouldDeletePath) {
            Storage::disk('local')->deleteDirectory($shouldDeletePath);
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
        $name = "{$fileName}.$format";

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
     * @throws Exception
     */
    protected function handleStyles(int $id, string $class): void
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
                    $this->initializeFFMpegQueue($id, $class);
                }
                else {
                    $this->handleVideoStyles($id, $this->file);
                }

                break;
        }
    }

    /**
     * Initialize FFMPEG Queue
     *
     * @param int $id
     * @param string $class
     */
    protected function initializeFFMpegQueue(int $id, string $class)
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
            $driver = $this->diskToDriver($this->disk);

            // save a copy of original file to use it on process ffmpeg queue, then delete it
            if ($driver != LaruploadEnum::LOCAL_DRIVER) {
                $path = $this->getBasePath($id, LaruploadEnum::ORIGINAL_FOLDER);
                Storage::disk(LaruploadEnum::LOCAL_DISK)->putFileAs($path, $this->file, $this->output['name']);
            }

            $statusId = DB::table('larupload_ffmpeg_queue')->insertGetId([
                'record_id'    => $id,
                'record_class' => $class,
                'created_at'   => now(),
            ]);

            ProcessFFMpeg::dispatch($statusId, $id, $this->name, $class, $this->folder, $this->injectedOptions, $this->output)->delay(now()->addSeconds(1));
        }
        else {
            throw new HttpResponseException(redirect(URL::previous())->withErrors([
                'ffmpeg_queue_max_num' => trans('larupload::messages.max-queue-num-exceeded')
            ]));
        }
    }

    /**
     * Handle styles for videos
     *
     * @param $id
     * @param UploadedFile $file
     * @throws Exception
     */
    protected function handleVideoStyles($id, UploadedFile $file): void
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
     * Clean directory before upload
     *
     * @param $id
     */
    protected function clean($id): void
    {
        $path = $this->getBasePath($id);

        Storage::disk($this->disk)->deleteDirectory($path);
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
     * Check if style has file
     *
     * @param Model $model
     * @param $style
     * @return bool
     */
    protected function hasFile(Model $model, $style): bool
    {
        if (array_key_exists($style, $this->styles)) {
            $mime = null;
            if ($this->mode == 'heavy') {
                $mime = $model->{"{$this->name}_file_mime_type"};
            }
            else {
                $details = json_decode($model->{"{$this->name}_file_meta"});
                if (isset($details->mime_type) and $details->mime_type) {
                    $mime = $details->mime_type;
                }
            }

            if ($mime) {
                $type = $this->mimeToType($mime);

                if (isset($this->styles[$style]['type']) and !in_array($type, $this->styles[$style]['type'])) {
                    return false;
                }
            }
            else {
                return false;
            }
        }

        return true;
    }

    /**
     * Convert path to URL based on storage driver
     *
     * @param string $path
     * @return string|null
     */
    protected function storageUrl(string $path)
    {
        $storage = $this->disk;

        if ($this->file == LARUPLOAD_NULL) {
            return null;
        }

        if ($storage == 'local') {
            $url = Storage::disk($storage)->url($path);
            return url($url);
        }

        $base = config("filesystems.disks.$storage.url");
        if ($base) {
            return "$base/$path";
        }

        return $path;
    }

    /**
     * Download path based on storage driver
     *
     * @param string $path
     * @return RedirectResponse|StreamedResponse|null
     */
    protected function storageDownload(string $path)
    {
        $storage = $this->disk;

        if ($this->file == LARUPLOAD_NULL) {
            return null;
        }

        if ($storage == 'local') {
            return Storage::disk($storage)->download($path);
        }

        $base = config("filesystems.disks.$storage.url");
        if ($base) {
            return redirect("$base/$path");
        }

        return null;
    }

    /**
     * Retrieve Type and Name of attached style
     *
     * @param Model $model
     * @param $style
     * return array
     */
    protected function getTypeAndName(Model $model, $style)
    {
        $name = null;

        if ($this->mode == 'heavy') {
            $type = $model->{"{$this->name}_file_type"};

            if ($style == 'cover') {
                $name = $model->{"{$this->name}_file_cover"};
            }
            else {
                $name = $model->{"{$this->name}_file_name"};
            }
        }
        else {
            $details = json_decode($model->{"{$this->name}_file_meta"});
            $type = null;

            if ($details and isset($details->type)) {
                $type = $details->type;
            }

            if ($style == 'cover' and isset($details->cover) and $details->cover) {
                $name = $details->cover;
            }
            else if (isset($details->name) and $details->name) {
                $name = $details->name;
            }
        }

        return compact('name', 'type');
    }
}
