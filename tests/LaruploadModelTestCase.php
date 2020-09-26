<?php

namespace Mostafaznv\Larupload\Test;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Imagine\Gd\Imagine;
use Imagine\Image\ImageInterface;
use Mostafaznv\Larupload\Storage\FFMpeg;
use Mostafaznv\Larupload\Test\Models\LaruploadUploadHeavy;
use Mostafaznv\Larupload\Test\Models\LaruploadUploadLight;

trait LaruploadModelTestCase
{
    /**
     * @var string
     */
    public $mode;

    /**
     * @var Model
     */
    public $model;

    /**
     * @var UploadedFile
     */
    public $imagePNG;

    /**
     * @var UploadedFile
     */
    public $imageJPG;

    /**
     * @var UploadedFile
     */
    public $imageFaTitledJPG;

    /**
     * @var UploadedFile
     */
    public $imageSVG;

    /**
     * @var array
     */
    public $imageDetails;

    /**
     * @var UploadedFile
     */
    public $video;

    /**
     * @var UploadedFile
     */
    public $audio;

    /**
     * @var array
     */
    public $audioDetails;

    /**
     * @var UploadedFile
     */
    public $pdf;

    /**
     * @var array
     */
    public $pdfDetails;

    /**
     * @var string
     */
    public $hexRegex = '/^#[0-9A-F]{6}$/i';

    protected function initModel(array $config = []): Model
    {
        if ($this->mode == 'heavy') {
            $this->model = new LaruploadUploadHeavy($config);
        }
        else {
            $this->model = new LaruploadUploadLight($config);
        }

        return $this->model;
    }

    protected function initFiles()
    {
        $this->imageJPG = new UploadedFile(realpath(__DIR__ . '/Data/image.jpg'), 'image.jpg');
        $this->imageFaTitledJPG = new UploadedFile(realpath(__DIR__ . '/Data/باشگاه بارسلونا.jpg'), 'باشگاه بارسلونا.jpg');
        $this->imagePNG = new UploadedFile(realpath(__DIR__ . '/Data/image.png'), 'image.png');
        $this->imageSVG = new UploadedFile(realpath(__DIR__ . '/Data/image.svg'), 'image.svg');
        $this->video = new UploadedFile(realpath(__DIR__ . '/Data/video-1.mp4'), 'video-1.mp4');
        $this->audio = new UploadedFile(realpath(__DIR__ . '/Data/audio-1.mp3'), 'audio-1.mp3');
        $this->pdf = new UploadedFile(realpath(__DIR__ . '/Data/pdf-1.pdf'), 'pdf-1.pdf');

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
                    'slug' => 'باشگاه-بارسلونا',
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
        $this->model->file = $this->imageJPG;
        $this->model->save();

        return $this->model;
    }

    protected function uploadFaTitledJPG(): Model
    {
        $this->model->file = $this->imageFaTitledJPG;
        $this->model->save();

        return $this->model;
    }

    protected function uploadPNG(): Model
    {
        $this->model->file = $this->imagePNG;
        $this->model->save();

        return $this->model;
    }

    protected function uploadSVG(): Model
    {
        $this->model->file = $this->imageSVG;
        $this->model->save();

        return $this->model;
    }

    protected function uploadVideo(): Model
    {
        $this->model->file = $this->video;
        $this->model->save();

        return $this->model;
    }

    protected function uploadAudio(): Model
    {
        $this->model->file = $this->audio;
        $this->model->save();

        return $this->model;
    }

    protected function uploadPDF(bool $withCover = false): Model
    {
        if ($withCover) {
            $this->model->setUploadedFile('file', $this->pdf, $this->imageJPG);
        }
        else {
            $this->model->setUploadedFile('file', $this->pdf);
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
        $file = new UploadedFile($path, pathinfo($path, PATHINFO_FILENAME));

        $ffmpeg = new FFMpeg($file);
        return $ffmpeg->getMeta();
    }
}
