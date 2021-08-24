<?php

namespace Mostafaznv\Larupload\Test;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Imagine\Gd\Imagine;
use Imagine\Image\ImageInterface;
use Mostafaznv\Larupload\LaruploadEnum;
use Mostafaznv\Larupload\Storage\Attachment;
use Mostafaznv\Larupload\Storage\FFMpeg;
use Mostafaznv\Larupload\Test\Models\LaruploadUploadHeavy;
use Mostafaznv\Larupload\Test\Models\LaruploadUploadLight;
use Mostafaznv\Larupload\Test\Models\LaruploadUploadSoftDelete;

trait LaruploadModelTestCaseTools
{
    /**
     * @var string
     */
    public string $mode;

    /**
     * @var Model
     */
    public Model $model;

    /**
     * @var UploadedFile
     */
    public UploadedFile $imagePNG;

    /**
     * @var UploadedFile
     */
    public UploadedFile $imageJPG;

    /**
     * @var UploadedFile
     */
    public UploadedFile $imageFaTitledJPG;

    /**
     * @var UploadedFile
     */
    public UploadedFile $imageSVG;

    /**
     * @var array
     */
    public array $imageDetails;

    /**
     * @var UploadedFile
     */
    public UploadedFile $video;

    /**
     * @var UploadedFile
     */
    public UploadedFile $audio;

    /**
     * @var array
     */
    public array $audioDetails;

    /**
     * @var UploadedFile
     */
    public UploadedFile $pdf;

    /**
     * @var array
     */
    public array $pdfDetails;

    /**
     * @var string
     */
    public string $hexRegex = '/^#[0-9A-F]{6}$/i';

    protected function initModel(): Model
    {
        if ($this->mode == LaruploadEnum::HEAVY_MODE) {
            $this->model = new LaruploadUploadHeavy;
        }
        else {
            $this->model = new LaruploadUploadLight;
        }

        return $this->model;
    }

    protected function initSoftDeleteModel(): Model
    {
        $this->model = new LaruploadUploadSoftDelete;

        return $this->model;
    }

    protected function initFiles()
    {
        $this->imageJPG = new UploadedFile(realpath(__DIR__ . '/Data/image.jpg'), 'image.jpg', 'image/jpeg', null, true);
        $this->imageFaTitledJPG = new UploadedFile(realpath(__DIR__ . '/Data/farsi-name.jpeg'), 'تیم بارسلونا.jpeg', 'image/jpeg', null, true);
        $this->imagePNG = new UploadedFile(realpath(__DIR__ . '/Data/image.png'), 'image.png', 'image/png', null, true);
        $this->imageSVG = new UploadedFile(realpath(__DIR__ . '/Data/image.svg'), 'image.svg', 'image/svg+xml', null, true);
        $this->video = new UploadedFile(realpath(__DIR__ . '/Data/video-1.mp4'), 'video-1.mp4', 'video/mp4', null, true);
        $this->audio = new UploadedFile(realpath(__DIR__ . '/Data/audio-1.mp3'), 'audio-1.mp3', 'audio/mpeg', null, true);
        $this->pdf = new UploadedFile(realpath(__DIR__ . '/Data/pdf-1.pdf'), 'pdf-1.pdf', 'application/pdf', null, true);

        $this->imageDetails = [
            'cover' => [
                'width'  => 500,
                'height' => 500,
            ],

            'jpg' => [
                'size'      => 35700,
                'width'     => 1077,
                'height'    => 791,
                'mime_type' => 'image/jpeg',
                'color'     => '#f4c00a',
                'name'      => [
                    'hash' => '9e55cf595703eaa109025073caed65a4.jpg',
                    'slug' => 'image',
                ]
            ],

            'jpg-fa' => [
                'size'      => 35700,
                'width'     => 1077,
                'height'    => 791,
                'mime_type' => 'image/jpeg',
                'color'     => '#f4c00a',
                'name'      => [
                    'hash' => '9e55cf595703eaa109025073caed65a4.jpg',
                    'slug' => 'تیم-بارسلونا',
                ]
            ],

            'png' => [
                'size'      => 44613,
                'width'     => 1077,
                'height'    => 791,
                'mime_type' => 'image/png',
                'color'     => '#212e4b',
                'name'      => [
                    'hash' => 'ac0c1777d6e82e59f45cf4b155079af4.png',
                ]
            ],

            'svg' => [
                'size'      => 11819,
                'width'     => 1077,
                'height'    => 791,
                'mime_type' => 'image/svg',
                'color'     => '#212d4b',
                'name'      => [
                    'hash' => '341a0d4d58d60c0595586725e8737d8c.svg',
                ]
            ],
        ];

        $this->audioDetails = [
            'name'      => 'cd3eb553923c076068f8a7057fcd7113.mp3',
            'size'      => 470173,
            'mime_type' => 'audio/mpeg',
            'duration'  => 67,
        ];

        $this->pdfDetails = [
            'name'      => '4b41a3475132bd861b30a878e30aa56a.pdf',
            'size'      => 3028,
            'mime_type' => 'application/pdf',
        ];
    }

    protected function uploadJPG(): Model
    {
        $this->model->main_file = $this->imageJPG;
        $this->model->save();

        return $this->model;
    }

    protected function uploadJpgByFunction(): Model
    {
        $this->model->main_file->attach($this->imageJPG);
        $this->model->save();

        return $this->model;
    }

    protected function uploadFaTitledJPG(): Model
    {
        $this->model->main_file = $this->imageFaTitledJPG;
        $this->model->save();

        return $this->model;
    }

    protected function uploadPNG(): Model
    {
        $this->model->main_file = $this->imagePNG;
        $this->model->save();

        return $this->model;
    }

    protected function uploadSVG(): Model
    {
        $this->model->main_file = $this->imageSVG;
        $this->model->save();

        return $this->model;
    }

    protected function uploadVideo(): Model
    {
        $this->model->main_file = $this->video;
        $this->model->save();

        return $this->model;
    }

    protected function uploadAudio(): Model
    {
        $this->model->main_file = $this->audio;
        $this->model->save();

        return $this->model;
    }

    protected function uploadPDF(bool $withCover = false): Model
    {
        if ($withCover) {
            $this->model->main_file->attach($this->pdf, $this->imageJPG);
        }
        else {
            $this->model->main_file->attach($this->pdf);
        }

        $this->model->save();

        return $this->model;
    }

    protected function image(string $url): ImageInterface
    {
        $path = public_path(str_replace(url('/'), '', $url));

        $image = new Imagine();
        return $image->open($path);
    }

    protected function video(string $url): array
    {
        $path = public_path(str_replace(url('/'), '', $url));
        $file = new UploadedFile($path, pathinfo($path, PATHINFO_FILENAME), null, null, true);

        $ffmpeg = new FFMpeg($file, Config::get('larupload.disk'), Config::get('larupload.local-disk'));
        return $ffmpeg->getMeta();
    }

    protected function file(string $url): UploadedFile
    {
        $path = public_path(str_replace(url('/'), '', $url));
        return new UploadedFile($path, pathinfo($path, PATHINFO_FILENAME), null, null, true);
    }

    protected function allAttachmentPaths(Attachment $attachment): array
    {
        $paths = [];

        foreach ($attachment->urls() as $name => $url) {
            if ($url and $name != 'meta') {
                $paths[] = public_path(str_replace(url('/'), '', $url));
            }
        }

        return $paths;
    }
}
