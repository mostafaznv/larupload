<?php

namespace Mostafaznv\Larupload\Storage;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Mostafaznv\Larupload\Helpers\Helper;
use Illuminate\Http\UploadedFile;
use Mostafaznv\Larupload\Helpers\Str;

class Attachment
{
    /**
     * Column name.
     *
     * @var string
     */
    protected $name;

    /**
     * File path.
     *
     * @var string
     */
    protected $path;

    /**
     * Larupload configurations.
     *
     * @var array
     */
    protected $config;

    /**
     * Storage driver.
     *
     * @var string
     */
    protected $storage;

    /**
     * Details mode, light or heavy.
     *
     * @var string
     */
    protected $mode;

    /**
     * Naming method.
     *
     * @var string
     */
    protected $namingMethod;

    /**
     * Style options.
     *
     * @var array
     */
    protected $styles;

    /**
     * dominant color flag.
     *
     * @var boolean
     */
    protected $dominantColor;

    /**
     * Generate cover flag.
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
     * Keep old files or not.
     *
     * @var boolean
     */
    protected $keepOldFiles;

    /**
     * Preserve files or not.
     *
     * @var boolean
     */
    protected $preserveFiles;

    /**
     * Allowed mime types.
     *
     * @var array
     */
    protected $allowedMimeTypes;

    /**
     * Allowed file extensions.
     *
     * @var array
     */
    protected $allowedMimes;

    /**
     * File object.
     *
     * @var object
     */
    protected $file;

    /**
     * Predefined cover file by user.
     *
     * @var object
     */
    protected $cover;

    /**
     * Type to get type of attached file.
     *
     * @var string
     */
    protected $type;

    /**
     * Output array to save in database.
     *
     * @var array
     */
    protected $output = [
        'name'           => null,
        'size'           => null,
        'type'           => null,
        'width'          => null,
        'height'         => null,
        'duration'       => null,
        'dominant_color' => null,
        'format'         => null,
        'cover'          => null,
    ];


    /**
     * Attachment constructor.
     *
     * @param $name
     * @param $folder
     * @param array $options
     * @throws \Exception
     */
    public function __construct($name, $folder, Array $options = [])
    {

        $this->config = config('larupload');

        if (Helper::validate($options)) {
            $options = Helper::arrayMergeRecursiveDistinct($this->getDefaultOptions(), $options);

            $this->name = $name;
            $this->path = $this->config['path'] . "/" . strtolower($folder);

            $this->storage = $options['storage'];
            $this->mode = $options['mode'];
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
    }

    /**
     * Set uploaded file.
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
            }
            else {
                if (!$this->keepOldFiles)
                    $this->clean($model->id);

                $this->setBasicDetails();

                $this->setMediaDetails();

                $this->handleStyles($model->id);

                $this->setCover($model->id);
            }

            $model = $this->setAttributes($model);
        }

        return $model;
    }

    /**
     * After delete event to delete files.
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
     * Generate URL for attached file.
     * Remember, if you are using the local driver, all files that should be publicly accessible should be placed in the storage/app/public directory. Furthermore, you should create a symbolic link at public/storage which points to the  storage/app/public directory.
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
                if ($style == 'cover')
                    $name = $model->{"{$this->name}_file_cover"};
                else
                    $name = $model->{"{$this->name}_file_name"};
            }
            else {
                $details = json_decode($model->{"{$this->name}_file_meta"});
                if ($style == 'cover' and isset($details->cover) and $details->cover) {
                    $name = $details->cover;
                }
                else if (isset($details->name) and $details->name) {
                    $name = $details->name;
                }
            }

            if ($name and $this->hasFile($model, $style)) {
                $path = $this->getPath($model->id, $style);
                $path = "$path/$name";

                $url = $this->storageUrl($path);

                return $url;
            }
        }

        return null;
    }

    /**
     * Get All styles (original, cover and ...) for attached field.
     *
     * @param Model $model
     * @return array
     */
    public function getFiles(Model $model)
    {
        $styleNames = array_merge(['original', 'cover'], array_keys($this->styles));
        $styles = [];

        foreach ($styleNames as $style) {
            if ($style == 'cover' and $this->generateCover == false) {
                $styles[$style] = null;
                continue;
            }

            $styles[$style] = $this->url($model, $style);
        }

        return $styles;
    }

    /**
     * Get meta data as an array or object.
     *
     * @param Model $model
     * @param null $key
     * @return array|mixed|null
     */
    public function getMeta(Model $model, $key = null)
    {
        if ($this->mode == 'heavy') {
            $meta = $this->output;

            if ($key) {
                if (array_key_exists($key, $meta)) {
                    return $model->{"{$this->name}_file_$key"};
                }

                return null;
            }
            else {
                foreach ($meta as $index => $item) {
                    $meta[$index] = $model->{"{$this->name}_file_$index"};
                }

                return $meta;
            }
        }
        else {
            $meta = json_decode($model->{"{$this->name}_file_meta"}, true);

            if ($key) {
                return (isset($meta[$key])) ? $meta[$key] : null;
            }
            else {
                return $meta;
            }
        }
    }

    /**
     * Validate files with mime type and file extension.
     *
     * @param UploadedFile $file
     * @return bool
     */
    protected function validation(UploadedFile $file) : bool
    {
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

        return true;
    }

    /**
     * Get default larupload options.
     *
     * @return array
     */
    protected function getDefaultOptions(): array
    {
        $defaultOptions = [
            'storage'            => $this->config['storage'],
            'mode'               => $this->config['mode'],
            'naming_method'      => $this->config['naming_method'],
            'styles'             => $this->config['styles'],
            'dominant_color'     => $this->config['dominant_color'],
            'generate_cover'     => $this->config['generate_cover'],
            'cover_style'        => $this->config['cover_style'],
            'keep_old_files'     => $this->config['keep_old_files'],
            'preserve_files'     => $this->config['preserve_files'],
            'allowed_mime_types' => $this->config['allowed_mime_types'],
            'allowed_mimes'      => $this->config['allowed_mimes'],
        ];

        return $defaultOptions;
    }

    /**
     * Get file type.
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
     * Convert MimeType to human readable type.
     *
     * @param $mime
     * @return string
     */
    protected function mimeToType($mime)
    {
        if (strstr($mime, "image/"))
            return 'image';
        else if (strstr($mime, "video/"))
            return 'video';
        else if (strstr($mime, "audio/"))
            return 'audio';
        else
            return 'file';
    }

    /**
     * Set some basic details.
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
        $this->output['type'] = $this->file->getClientMimeType();
    }

    /**
     * Set media details.
     */
    protected function setMediaDetails()
    {
        switch ($this->type) {
            case 'video':
            case 'audio':
                $ffmpeg = new FFMpeg($this->file);
                $meta = $ffmpeg->getMeta();

                $this->output['width'] = $meta['width'];
                $this->output['height'] = $meta['height'];
                $this->output['duration'] = $meta['duration'];

                break;

            case 'image':
                $image = new Image($this->file);
                $meta = $image->getMeta();

                $dominantColor = ($this->dominantColor) ? Image::dominant($this->file) : null;

                $this->output['width'] = $meta['width'];
                $this->output['height'] = $meta['height'];
                $this->output['dominant_color'] = $dominantColor;
                break;
        }
    }

    /**
     * Set cover photo.
     * Generate cover photo automatically from photos and videos, if cover file was null
     *
     * @param $id
     * @return bool
     */
    protected function setCover($id)
    {
        $path = $this->getPath($id, 'cover');

        $name = pathinfo($this->output['name'], PATHINFO_FILENAME);
        $name = "$name.jpg";

        if ($this->cover and ($this->cover instanceof UploadedFile) and ($this->mimeToType($this->cover->getClientMimeType()) == 'image')) {
            Storage::disk($this->storage)->putFileAs($path, $this->cover, $name);

            $this->output['cover'] = $name;
        }
        else {
            if (!$this->generateCover)
                return false;

            switch ($this->type) {
                case 'video':
                    Storage::disk($this->storage)->makeDirectory($path);
                    $saveTo = $path . "/$name";

                    $ffmpeg = new FFMpeg($this->file);
                    $result = $ffmpeg->capture('0.1', $this->coverStyle, $this->storage, $saveTo);

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

                    if ($result)
                        $this->output['cover'] = $name;

                    break;
            }
        }
    }

    /**
     * Handle styles.
     * resize, crop and generate styles from original file.
     *
     * @param $id
     */
    protected function handleStyles($id)
    {
        // Handle original.
        $path = $this->getPath($id, 'original');
        Storage::disk($this->storage)->putFileAs($path, $this->file, $this->output['name']);


        // Handle styles by file type.
        switch ($this->type) {
            case 'image':
                foreach ($this->styles as $name => $style) {
                    if (isset($style['type']) and !in_array($this->type, $style['type']))
                        continue;

                    $path = $this->getPath($id, $name);
                    Storage::disk($this->storage)->makeDirectory($path);
                    $saveTo = $path . '/' . $this->output['name'];

                    $image = new Image($this->file);
                    $image->resize($this->storage, $saveTo, $style);
                }

                break;


            case 'video':
                foreach ($this->styles as $name => $style) {
                    if (isset($style['type']) and !in_array($this->type, $style['type']))
                        continue;

                    $path = $this->getPath($id, $name);
                    Storage::disk($this->storage)->makeDirectory($path);
                    $saveTo = $path . '/' . $this->output['name'];

                    $ffmpeg = new FFMpeg($this->file);
                    $ffmpeg->manipulate($style, $this->storage, $saveTo);
                }

                break;
        }
    }

    /**
     * Path Helper to generate relative path string.
     *
     * @param $id
     * @param null $folder
     * @return string
     */
    protected function getPath($id, $folder = null)
    {
        if ($folder)
            return $this->path . '/' . $id . '/' . $this->name . '/' . $folder;
        return $this->path . '/' . $id . '/' . $this->name;
    }

    /**
     * Clean directory before upload.
     *
     * @param $id
     */
    protected function clean($id)
    {
        $path = $this->getPath($id);
        Storage::disk($this->storage)->deleteDirectory($path);
    }

    /**
     * Set attributes before saving event.
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
            $model->{"{$this->name}_file_name"} = $this->output['name'];
            $model->{"{$this->name}_file_meta"} = json_encode($this->output);
        }

        return $model;
    }

    /**
     * Check if style has file.
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
                $mime = $model->{"{$this->name}_file_type"};
            }
            else {
                $details = json_decode($model->{"{$this->name}_file_meta"});
                if (isset($details->type) and $details->type)
                    $mime = $details->type;
            }

            if ($mime) {
                $type = $this->mimeToType($mime);

                if (isset($this->styles[$style]['type']) and !in_array($type, $this->styles[$style]['type']))
                    return false;
            }
            else {
                return false;
            }
        }

        return true;
    }

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
}