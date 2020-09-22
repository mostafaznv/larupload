<?php

namespace Mostafaznv\Larupload\Storage;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Mostafaznv\Larupload\Helpers\Helper;
use Illuminate\Http\UploadedFile;
use Mostafaznv\Larupload\Helpers\Str;
use Mostafaznv\Larupload\Jobs\ProcessFFMpeg;

class Attachment
{
    /**
     * Column name
     *
     * @var string
     */
    protected $name;

    /**
     * Folder Name (table name)
     *
     * @var string
     */
    protected $folder;

    /**
     * Options on the Fly
     *
     * @var string
     */
    protected $injectedOptions;

    /**
     * File path
     *
     * @var string
     */
    protected $path;

    /**
     * Larupload configurations
     *
     * @var array
     */
    protected $config;

    /**
     * Storage driver
     *
     * @var string
     */
    protected $storage;

    /**
     * Details mode, light or heavy
     *
     * @var string
     */
    protected $mode;

    /**
     * @var boolean
     */
    protected $withMeta;

    /**
     * Naming method
     *
     * @var string
     */
    protected $namingMethod;

    /**
     * Style options
     *
     * @var array
     */
    protected $styles;

    /**
     * dominant color flag
     *
     * @var boolean
     */
    protected $dominantColor;

    /**
     * Generate cover flag
     *
     * @var boolean
     */
    protected $generateCover;

    /**
     * Cover Style
     *
     * @var array
     */
    protected $coverStyle;

    /**
     * Keep old files or not
     *
     * @var boolean
     */
    protected $keepOldFiles;

    /**
     * Preserve files or not
     *
     * @var boolean
     */
    protected $preserveFiles;

    /**
     * Allowed mime types
     *
     * @var array
     */
    protected $allowedMimeTypes;

    /**
     * Allowed file extensions
     *
     * @var array
     */
    protected $allowedMimes;

    /**
     * File object
     *
     * @var UploadedFile
     */
    protected $file;

    /**
     * Predefined cover file by user
     *
     * @var object
     */
    protected $cover;

    /**
     * Type to get type of attached file
     *
     * @var string
     */
    protected $type;

    /**
     * Output array to save in database
     *
     * @var array
     */
    protected $output = [
        'name'           => null,
        'size'           => null,
        'type'           => null,
        'mime_type'      => null,
        'width'          => null,
        'height'         => null,
        'duration'       => null,
        'dominant_color' => null,
        'format'         => null,
        'cover'          => null,
    ];


    /**
     * Attachment constructor
     *
     * @param $name
     * @param $folder
     * @param array $options
     * @throws Exception
     */
    public function __construct($name, $folder, array $options = [])
    {
        $this->config = config('larupload');
        $errors = Helper::validate($options);

        if (empty($errors)) {
            $this->folder = $folder;
            $this->injectedOptions = $options;

            $options = Helper::arrayMergeRecursiveDistinct($this->getDefaultOptions(), $options);

            $this->name = $name;
            $this->path = $this->config['path'] . "/" . strtolower($folder);

            $this->storage = $options['storage'];
            $this->mode = $options['mode'];
            $this->withMeta = $options['with_meta'];
            $this->namingMethod = $options['naming_method'];
            $this->dominantColor = $options['dominant_color'];
            $this->styles = $options['styles'];
            $this->generateCover = $options['generate_cover'];
            $this->coverStyle = $options['cover_style'];
            $this->keepOldFiles = $options['keep_old_files'];
            $this->preserveFiles = $options['preserve_files'];
            $this->allowedMimeTypes = $options['allowed_mime_types'];
            $this->allowedMimes = $options['allowed_mimes'];
        }
        else {
            $fields = implode(', ', array_keys($errors));

            throw new Exception("invalid fields: $fields");
        }
    }

    /**
     * Set uploaded file
     *
     * @param UploadedFile $file
     * @param UploadedFile|null $cover
     */
    public function setUploadedFile($file, $cover = null)
    {
        if (($file instanceof UploadedFile or $file == LARUPLOAD_NULL) and ($cover instanceof UploadedFile or $cover == null)) {
            if ($this->validation($file)) {
                $this->file = $file;

                if ($file != LARUPLOAD_NULL) {
                    $this->cover = $cover;
                    $this->type = $this->getFileType($file);
                }
            }
        }
    }

    /**
     * After save event to process all uploads, converts and ...
     *
     * @param Model $model
     * @return Model
     */
    public function saved(Model $model)
    {
        if ($this->file) {
            if ($this->file == LARUPLOAD_NULL) {
                $this->clean($model->id);

                if ($this->mode == 'light') {
                    $this->output = null;
                }
            }
            else {
                if (!$this->keepOldFiles) {
                    $this->clean($model->id);
                }

                $this->setBasicDetails();

                $this->setMediaDetails();

                $this->handleStyles($model->id, $model->getMorphClass());

                $this->setCover($model->id);
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
    public function deleted(Model $model)
    {
        if (!$this->preserveFiles) {
            $path = $this->path . '/' . $model->id;
            Storage::disk($this->storage)->deleteDirectory($path);
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
    public function url(Model $model, $style = 'original')
    {
        if (in_array($style, ['original', 'cover']) or array_key_exists($style, $this->styles)) {
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

                $url = $this->storageUrl($path);

                return $url;
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
    public function getFiles(Model $model)
    {
        $styleNames = array_merge(['original', 'cover'], array_keys($this->styles));
        $styles = new \stdClass();

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
     * @param null $key
     * @return array|mixed|null
     */
    public function getMeta(Model $model, $key = null)
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
                    $meta->{$index} = $model->{"{$this->name}_file_$index"};
                }

                return $meta;
            }
        }
        else {
            $meta = json_decode($model->{"{$this->name}_file_meta"});

            if ($key) {
                return property_exists($meta, $key) ? $meta->{$key} : null;
            }
            else {
                return $meta;
            }
        }
    }

    /**
     * Handle FFMpeg queue on running ffmpeg queue:work
     *
     * @param $id
     * @param $meta
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function handleFFMpegQueue($id, $meta)
    {
        $shouldDeletePath = null;
        $path = $this->getPath($id, 'original');

        if ($this->storage == 'local') {
            $path = Storage::disk($this->storage)->path("$path/{$meta['name']}");
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
     * Validate files with mime type and file extension
     *
     * @param UploadedFile $file
     * @return bool
     */
    protected function validation($file): bool
    {
        if ($file != LARUPLOAD_NULL) {
            if (count($this->allowedMimes)) {
                if (!in_array($file->getClientOriginalExtension(), $this->allowedMimes)) {
                    return false;
                }
            }

            if (count($this->allowedMimeTypes)) {
                if (!in_array($file->getClientMimeType(), $this->allowedMimeTypes)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Get default larupload options
     *
     * @return array
     */
    protected function getDefaultOptions(): array
    {
        return [
            'storage'            => $this->config['storage'],
            'mode'               => $this->config['mode'],
            'naming_method'      => $this->config['naming_method'],
            'with_meta'          => $this->config['with_meta'],
            'styles'             => $this->config['styles'],
            'dominant_color'     => $this->config['dominant_color'],
            'generate_cover'     => $this->config['generate_cover'],
            'cover_style'        => $this->config['cover_style'],
            'keep_old_files'     => $this->config['keep_old_files'],
            'preserve_files'     => $this->config['preserve_files'],
            'allowed_mime_types' => $this->config['allowed_mime_types'],
            'allowed_mimes'      => $this->config['allowed_mimes'],
        ];
    }

    /**
     * Get file type
     *
     * @param UploadedFile $file
     * @return null|string
     */
    protected function getFileType(UploadedFile $file)
    {
        if ($file) {
            $mime = $file->getMimeType();

            return $this->mimeToType($mime);
        }

        return null;
    }

    /**
     * Convert MimeType to human readable type
     *
     * @param $mime
     * @return string
     */
    protected function mimeToType($mime)
    {
        if (strstr($mime, "image/")) {
            return 'image';
        }
        else if (strstr($mime, "video/")) {
            return 'video';
        }
        else if (strstr($mime, "audio/")) {
            return 'audio';
        }
        else {
            return 'file';
        }
    }

    /**
     * Set some basic details
     */
    protected function setBasicDetails()
    {
        $format = $this->file->getClientOriginalExtension();

        switch ($this->namingMethod) {
            case 'hash_file':
                $name = hash_file('md5', $this->file->getRealPath());
                break;

            case 'time':
                $name = time();
                break;


            default:
                $name = $this->file->getClientOriginalName();
                $name = pathinfo($name, PATHINFO_FILENAME);
                $num = random_int(0, 9999);

                $str = new Str($this->config['lang']);
                $name = $str->generateSlug($name) . "-" . $num;
                break;
        }

        $this->output['name'] = $name . "." . $format;
        $this->output['format'] = $format;
        $this->output['size'] = $this->file->getSize();
        $this->output['type'] = $this->getHumanReadableFileType($this->file->getMimeType());
        $this->output['mime_type'] = $this->file->getMimeType();
    }

    /**
     * Set media details
     */
    protected function setMediaDetails()
    {
        switch ($this->type) {
            case 'video':
            case 'audio':
                $ffmpeg = new FFMpeg($this->file);
                $meta = $ffmpeg->getMeta();

                $this->output['width'] = (int)$meta['width'];
                $this->output['height'] = (int)$meta['height'];
                $this->output['duration'] = (int)$meta['duration'];

                break;

            case 'image':
                $image = new Image($this->file);
                $meta = $image->getMeta();

                $dominantColor = $this->dominantColor ? Image::dominant($this->file) : null;

                $this->output['width'] = (int)$meta['width'];
                $this->output['height'] = (int)$meta['height'];
                $this->output['dominant_color'] = $dominantColor;
                break;
        }
    }

    /**
     * Set cover photo
     * Generate cover photo automatically from photos and videos, if cover file was null
     *
     * @param $id
     * @return bool
     */
    protected function setCover($id)
    {
        $path = $this->getPath($id, 'cover');

        $pathInfo = pathinfo($this->output['name']);
        $fileName = $pathInfo['filename'];

        if ($this->type == 'image') {
            $name = "$fileName." . ($pathInfo['extension'] == 'svg' ? 'png' : $pathInfo['extension']);
        }
        else {
            $name = "$fileName.jpg";
        }

        if ($this->cover and ($this->cover instanceof UploadedFile) and ($this->mimeToType($this->cover->getClientMimeType()) == 'image')) {
            Storage::disk($this->storage)->putFileAs($path, $this->cover, $name);

            $this->output['cover'] = $name;
        }
        else {
            if (!$this->generateCover) {
                return false;
            }

            switch ($this->type) {
                case 'video':
                    Storage::disk($this->storage)->makeDirectory($path);
                    $saveTo = $path . "/$name";

                    $ffmpeg = new FFMpeg($this->file);
                    $result = $ffmpeg->capture($this->config['ffmpeg-capture-frame'], $this->coverStyle, $this->storage, $saveTo);

                    if ($result) {
                        $this->output['cover'] = $name;
                        $this->output['dominant_color'] = ($this->dominantColor) ? Image::dominant($saveTo) : null;
                    }

                    break;


                case 'image':
                    Storage::disk($this->storage)->makeDirectory($path);
                    $saveTo = $path . "/$name";

                    $image = new Image($this->file);
                    $result = $image->resize($this->storage, $saveTo, $this->coverStyle);

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
     * @param $id
     * @param $class
     */
    protected function handleStyles($id, $class)
    {
        // Handle original
        $path = $this->getPath($id, 'original');
        Storage::disk($this->storage)->putFileAs($path, $this->file, $this->output['name']);


        // Handle styles by file type
        switch ($this->type) {
            case 'image':
                foreach ($this->styles as $name => $style) {
                    if ($name == 'stream' or (isset($style['type']) and !in_array($this->type, $style['type']))) {
                        continue;
                    }

                    $path = $this->getPath($id, $name);

                    Storage::disk($this->storage)->makeDirectory($path);
                    $saveTo = $path . '/' . $this->fixExceptionNames($this->output['name'], $style);

                    $image = new Image($this->file);
                    $image->resize($this->storage, $saveTo, $style);
                }

                break;


            case 'video':
                if ($this->config['ffmpeg-queue']) {
                    $maxQueueNum = $this->config['ffmpeg-max-queue-num'];
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
                        // Save a copy of original file to use it on process ffmpeg queue, then delete it
                        Storage::disk('local')->putFileAs($path, $this->file, $this->output['name']);

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
                else {
                    $this->handleVideoStyles($id, $this->file);
                }

                break;
        }
    }

    /**
     * Handle styles for videos
     *
     * @param $id
     * @param $file
     */
    protected function handleVideoStyles($id, $file)
    {
        foreach ($this->styles as $name => $style) {
            if ($name == 'stream' or (isset($style['type']) and !in_array($this->type, $style['type']))) {
                continue;
            }

            $path = $this->getPath($id, $name);
            Storage::disk($this->storage)->makeDirectory($path);
            $saveTo = $path . '/' . $this->output['name'];

            $ffmpeg = new FFMpeg($file);
            $ffmpeg->manipulate($style, $this->storage, $saveTo);

            unset($ffmpeg);
        }

        if (isset($this->styles['stream'])) {
            $fileName = pathinfo($this->output['name'], PATHINFO_FILENAME) . '.m3u8';

            $path = $this->getPath($id, 'stream');
            Storage::disk($this->storage)->makeDirectory($path);

            $ffmpeg = new FFMpeg($file);
            $ffmpeg->stream($this->styles['stream'], $this->storage, $path, $fileName);

            unset($ffmpeg);
        }
    }

    /**
     * Path Helper to generate relative path string
     *
     * @param $id
     * @param null $folder
     * @return string
     */
    protected function getPath($id, $folder = null)
    {
        if ($folder) {
            return $this->path . '/' . $id . '/' . $this->name . '/' . $folder;
        }

        return $this->path . '/' . $id . '/' . $this->name;
    }

    /**
     * Clean directory before upload
     *
     * @param $id
     */
    protected function clean($id)
    {
        $path = $this->getPath($id);
        Storage::disk($this->storage)->deleteDirectory($path);
    }

    /**
     * Set attributes before saving event
     *
     * @param Model $model
     * @return Model
     */
    protected function setAttributes(Model $model)
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
    protected function hasFile(Model $model, $style)
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
     * @param $path
     * @return string
     */
    protected function storageUrl($path)
    {
        $storage = $this->storage;

        if ($storage == 'local') {
            $url = Storage::disk($this->storage)->url($path);
            return url($url);
        }

        $base = config("filesystems.disks.$storage.url");
        if ($base) {
            return "$base/$path";
        }

        return $path;
    }

    /**
     * In some special cases we should use other file names instead of the original one
     * Example: when user uploads a svg image, we should change the converted format to jpg! so we have to manipulate file name
     *
     * @param $name
     * @param $style
     * @return mixed
     */
    protected function fixExceptionNames($name, $style)
    {
        if (!in_array($style, ['original', 'cover'])) {
            if (Str::endsWith($name, 'svg')) {
                $name = str_replace('svg', 'jpg', $name);
            }
        }

        return $name;
    }

    /**
     * Get human readable file type from mimetype
     *
     * @param $mimeType
     * @return null|string
     */
    protected function getHumanReadableFileType($mimeType)
    {
        if ($mimeType) {

            if (strstr($mimeType, "image/")) {
                return 'image';
            }
            else if (strstr($mimeType, "video/")) {
                return 'video';
            }
            else if (strstr($mimeType, "audio/")) {
                return 'audio';
            }
            else if ($mimeType == 'application/pdf') {
                return 'pdf';
            }
            else if ($mimeType == 'application/zip' or $mimeType == 'application/x-rar-compressed') {
                return 'compressed';
            }
            else {
                return 'file';
            }
        }

        return null;
    }

}
