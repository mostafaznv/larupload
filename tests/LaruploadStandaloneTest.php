<?php

namespace Mostafaznv\Larupload\Test;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Imagine\Gd\Imagine;
use Imagine\Image\ImageInterface;
use Mostafaznv\Larupload\Larupload;
use Mostafaznv\Larupload\LaruploadEnum;
use Mostafaznv\Larupload\Storage\FFMpeg;

class LaruploadStandaloneTest extends LaruploadTestCase
{
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

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrate();
        $this->initFiles();
    }

    protected function initFiles()
    {
        $this->imageJPG = new UploadedFile(realpath(__DIR__ . '/Data/image.jpg'), 'image.jpg', 'image/jpeg', null, true);
        $this->imageFaTitledJPG = new UploadedFile(realpath(__DIR__ . '/Data/تیم بارسلونا.jpeg'), 'تیم بارسلونا.jpeg', 'image/jpeg', null, true);
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

    /*
    * TEST UPLOAD
    */
    public function testUpload()
    {
        $upload = Larupload::init('uploader')->upload($this->imageJPG);

        $this->assertNotNull($upload->original);
    }


    /*
     * TEST FILE SIZE
     */
    public function testFileSize()
    {
        $upload = Larupload::init('uploader')->upload($this->imageJPG);

        $this->assertEquals($upload->meta->size, $this->imageDetails['jpg']['size']);
    }


    /*
     * TEST IMAGE WIDTH AND HEIGHT
     */
    public function testImageWidth()
    {
        $upload = Larupload::init('uploader')->upload($this->imageJPG);

        $this->assertEquals($upload->meta->width, $this->imageDetails['jpg']['width']);
    }

    public function testImageHeight()
    {
        $upload = Larupload::init('uploader')->upload($this->imageJPG);

        $this->assertEquals($upload->meta->height, $this->imageDetails['jpg']['height']);
    }


    /*
     * TEST MIME TYPE
     */
    public function testJpgMimeType()
    {
        $upload = Larupload::init('uploader')->upload($this->imageJPG);

        $this->assertEquals($upload->meta->mime_type, $this->imageDetails['jpg']['mime_type']);
    }


    /*
     * TEST FILE NAME
     */
    public function testHashName()
    {
        $upload = Larupload::init('uploader')
            ->namingMethod(LaruploadEnum::HASH_FILE_NAMING_METHOD)
            ->upload($this->imageJPG);

        $this->assertEquals($upload->meta->name, $this->imageDetails['jpg']['name']['hash']);
    }


    /*
     * TEST IMAGE DIMENSIONS
     */
    public function testCoverStyleDimensions()
    {
        $upload = Larupload::init('uploader')->upload($this->imageJPG);

        $image = $this->image($upload->cover);

        $this->assertNotNull($upload->cover);
        $this->assertEquals(500, $image->getSize()->getWidth());
        $this->assertEquals(500, $image->getSize()->getHeight());
    }

    public function testSmallStyleDimensions()
    {
        $upload = Larupload::init('uploader')
            ->style('small', 200, 200, LaruploadEnum::CROP_STYLE_MODE)
            ->upload($this->imageJPG);

        $image = $this->image($upload->small);

        $this->assertNotNull($upload->small);
        $this->assertEquals(200, $image->getSize()->getWidth());
        $this->assertEquals(200, $image->getSize()->getHeight());
    }

    public function testMediumStyleDimensions()
    {
        $upload = Larupload::init('uploader')
            ->style('medium', 800, 800, LaruploadEnum::AUTO_STYLE_MODE)
            ->upload($this->imageJPG);

        $image = $this->image($upload->medium);

        $this->assertNotNull($upload->medium);
        $this->assertEquals(799, $image->getSize()->getWidth());
        $this->assertEquals(587, $image->getSize()->getHeight());
    }

    public function testLandscapeStyleDimensions()
    {
        $upload = Larupload::init('uploader')
            ->style('landscape', 400, null, LaruploadEnum::LANDSCAPE_STYLE_MODE)
            ->upload($this->imageJPG);

        $image = $this->image($upload->landscape);

        $this->assertNotNull($upload->landscape);
        $this->assertEquals(399, $image->getSize()->getWidth());
        $this->assertEquals(293, $image->getSize()->getHeight());
    }

    public function testPortraitStyleDimensions()
    {
        $upload = Larupload::init('uploader')
            ->style('portrait', null, 400, LaruploadEnum::PORTRAIT_STYLE_MODE)
            ->upload($this->imageJPG);

        $image = $this->image($upload->portrait);

        $this->assertNotNull($upload->portrait);
        $this->assertEquals(544, $image->getSize()->getWidth());
        $this->assertEquals(399, $image->getSize()->getHeight());
    }

    public function testExactStyleDimensions()
    {
        $upload = Larupload::init('uploader')
            ->style('exact', 300, 190, LaruploadEnum::EXACT_STYLE_MODE)
            ->upload($this->imageJPG);

        $image = $this->image($upload->exact);

        $this->assertNotNull($upload->exact);
        $this->assertEquals(300, $image->getSize()->getWidth());
        $this->assertEquals(190, $image->getSize()->getHeight());
    }

    public function testAutoStyleDimensions()
    {
        $upload = Larupload::init('uploader')
            ->style('auto', 300, 190, LaruploadEnum::AUTO_STYLE_MODE)
            ->upload($this->imageJPG);

        $image = $this->image($upload->auto);

        $this->assertNotNull($upload->auto);
        $this->assertEquals(300, $image->getSize()->getWidth());
        $this->assertEquals(220, $image->getSize()->getHeight());
    }


    /*
     * TEST DOMINANT COLOR
     */
    public function testJpgDominantColor()
    {
        $upload = Larupload::init('uploader')->upload($this->imageJPG);

        $this->assertEquals($upload->meta->dominant_color, $this->imageDetails['jpg']['color']);
    }

    public function testPngDominantColor()
    {
        $upload = Larupload::init('uploader')->upload($this->imagePNG);

        $this->assertEquals($upload->meta->dominant_color, $this->imageDetails['png']['color']);
    }

    public function testSvgDominantColor()
    {
        $upload = Larupload::init('uploader')
            ->imageProcessingLibrary(LaruploadEnum::IMAGICK_IMAGE_LIBRARY)
            ->upload($this->imageSVG);

        $this->assertEquals(true, !!preg_match($this->hexRegex, $upload->meta->dominant_color));
    }


    /*
     * TEST UPDATE/DELETE COVER
     */
    public function testUpdateCover()
    {
        $upload = Larupload::init('uploader')
            ->namingMethod(LaruploadEnum::HASH_FILE_NAMING_METHOD)
            ->upload($this->imageJPG);

        $this->assertEquals($this->imageDetails['jpg']['name']['hash'], $upload->meta->cover);

        $upload = Larupload::init('uploader')
            ->namingMethod(LaruploadEnum::HASH_FILE_NAMING_METHOD)
            ->changeCover($this->imagePNG);

        $this->assertEquals($this->imageDetails['jpg']['name']['hash'], $upload->meta->name);
        $this->assertEquals($this->imageDetails['png']['name']['hash'], $upload->meta->cover);
    }

    public function testDeleteCover()
    {
        $upload = Larupload::init('uploader')->upload($this->imageJPG);

        $this->assertNotNull($upload->cover);
        $this->assertNotNull($upload->meta->cover);

        $upload = Larupload::init('uploader')->deleteCover();

        $this->assertNull($upload->cover);
        $this->assertNull($upload->meta->cover);
    }


    /*
     * TEST AUDIO
     */
    public function testAudio()
    {
        $upload = Larupload::init('uploader')
            ->namingMethod(LaruploadEnum::HASH_FILE_NAMING_METHOD)
            ->upload($this->audio);

        $this->assertEquals($upload->meta->name, $this->audioDetails['name']);
        $this->assertEquals($upload->meta->size, $this->audioDetails['size']);
        $this->assertEquals($upload->meta->mime_type, $this->audioDetails['mime_type']);
        $this->assertEquals($upload->meta->duration, $this->audioDetails['duration']);
    }


    /*
     * TEST VIDEO STYLES
     */
    public function testVideoStyles()
    {
        $upload = Larupload::init('uploader')
            ->namingMethod(LaruploadEnum::HASH_FILE_NAMING_METHOD)
            ->style('small_size', 200, 200, LaruploadEnum::CROP_STYLE_MODE, [LaruploadEnum::IMAGE_STYLE_TYPE, LaruploadEnum::VIDEO_STYLE_TYPE])
            ->style('small', 200, 200, LaruploadEnum::CROP_STYLE_MODE, [LaruploadEnum::IMAGE_STYLE_TYPE, LaruploadEnum::VIDEO_STYLE_TYPE])
            ->style('medium', 800, 800, LaruploadEnum::AUTO_STYLE_MODE)
            ->style('landscape', 400, null, LaruploadEnum::LANDSCAPE_STYLE_MODE)
            ->style('portrait', null, 400, LaruploadEnum::PORTRAIT_STYLE_MODE)
            ->style('exact', 300, 190, LaruploadEnum::EXACT_STYLE_MODE)
            ->style('auto', 300, 190, LaruploadEnum::AUTO_STYLE_MODE)
            ->upload($this->video);

        // cover
        $meta = $this->video($upload->cover);
        $this->assertNotNull($upload->cover);
        $this->assertEquals(500, $meta['width']);
        $this->assertEquals(500, $meta['height']);

        // small
        $meta = $this->video($upload->small);
        $this->assertNotNull($upload->small);
        $this->assertEquals(200, $meta['width']);
        $this->assertEquals(200, $meta['height']);
        $this->assertEquals(5, $meta['duration']);

        // medium
        $meta = $this->video($upload->medium);
        $this->assertNotNull($upload->medium);
        $this->assertEquals(800, $meta['width']);
        $this->assertEquals(458, $meta['height']);
        $this->assertEquals(5, $meta['duration']);

        // landscape
        $meta = $this->video($upload->landscape);
        $this->assertNotNull($upload->landscape);
        $this->assertEquals(400, $meta['width']);
        $this->assertEquals(228, $meta['height']);
        $this->assertEquals(5, $meta['duration']);

        // portrait
        $meta = $this->video($upload->portrait);
        $this->assertNotNull($upload->portrait);
        $this->assertEquals(700, $meta['width']);
        $this->assertEquals(400, $meta['height']);
        $this->assertEquals(5, $meta['duration']);

        // exact
        $meta = $this->video($upload->exact);
        $this->assertNotNull($upload->exact);
        $this->assertEquals(300, $meta['width']);
        $this->assertEquals(172, $meta['height']);
        $this->assertEquals(5, $meta['duration']);

        // auto
        $meta = $this->video($upload->auto);
        $this->assertNotNull($upload->auto);
        $this->assertEquals(300, $meta['width']);
        $this->assertEquals(172, $meta['height']);
        $this->assertEquals(5, $meta['duration']);
    }


    /*
     * TEST STREAM
     */
    public function testUploadVideoStream()
    {
        $upload = Larupload::init('uploader')
            ->namingMethod(LaruploadEnum::HASH_FILE_NAMING_METHOD)
            ->stream('480p', 640, 480, '64k', '300000')
            ->stream('720p', 1280, 720, '64K', '1M')
            ->upload($this->video);


        $path = public_path(str_replace(url('/'), '', $upload->stream));
        $dir = pathinfo($path, PATHINFO_DIRNAME);

        $m3u8 = 'chunk-list.m3u8';
        $ts = 'file-sequence-0.ts';
        $folders = ['480p', '720p'];

        $this->assertEquals(true, file_exists($path));

        foreach ($folders as $folder) {
            $this->assertEquals(true, file_exists($dir . '/' . $folder . '/' . $m3u8));
            $this->assertEquals(true, file_exists($dir . '/' . $folder . '/' . $ts));
        }
    }


    /*
     * TEST UPLOAD WITH COVER
     */
    public function testUploadWithCover()
    {
        $upload = Larupload::init('uploader')->upload($this->pdf);
        $this->assertNull($upload->cover);

        $upload = Larupload::init('uploader')->upload($this->pdf, $this->imageJPG);
        $this->assertNotNull($upload->cover);
    }


    /*
     * TEST CUSTOM COVER STYLE
     */
    public function testCustomCoverStyle()
    {
        $upload = Larupload::init('uploader')
            ->coverStyle(200, 150, LaruploadEnum::EXACT_STYLE_MODE)
            ->upload($this->imageJPG);

        $cover = $upload->cover;
        $image = $this->image($cover);

        $this->assertNotNull($cover);
        $this->assertEquals(200, $image->getSize()->getWidth());
        $this->assertEquals(150, $image->getSize()->getHeight());
    }


    /*
     * TEST NAMING METHODS
     */
    public function testNamingMethods()
    {
        $time = time();

        $upload = Larupload::init('uploader')->namingMethod(LaruploadEnum::SLUG_NAMING_METHOD)->upload($this->imageJPG);
        $this->assertEquals(true, Str::contains($upload->meta->name, $this->imageDetails['jpg']['name']['slug']));

        Config::set('larupload.lang', 'fa');
        $upload = Larupload::init('uploader')->namingMethod(LaruploadEnum::SLUG_NAMING_METHOD)->upload($this->imageFaTitledJPG);
        $this->assertEquals(true, Str::contains($upload->meta->name, $this->imageDetails['jpg-fa']['name']['slug']));


        $upload = Larupload::init('uploader')->namingMethod(LaruploadEnum::TIME_NAMING_METHOD)->upload($this->imageJPG);
        $this->assertTrue((int)str_replace('.jpg', '', $upload->meta->name) >= $time);
    }


    /*
     * TEST CAMEL CASE RESPONSE
     */
    public function testCamelCaseResponse()
    {
        $upload = Larupload::init('uploader')->upload($this->imageJPG);
        $this->assertTrue($upload->meta->mime_type == $this->imageDetails['jpg']['mime_type']);

        Config::set('larupload.camel-case-response', true);

        $upload = Larupload::init('uploader')->upload($this->imageJPG);
        $this->assertTrue($upload->meta->mimeType == $this->imageDetails['jpg']['mime_type']);
    }


    /*
     * TEST FOLDER NAME
     */
    public function testKebabCaseFolderName()
    {
        $upload = Larupload::init('uploader')
            ->style('small_size', 200, 200)
            ->upload($this->imageJPG);

        $this->assertTrue(Str::contains($upload->small_size, '/small-size/'));
    }


    /*
     * TEST DELETE FILE
     */
    public function testDeleteFile()
    {
        $upload = Larupload::init('uploader')->upload($this->imageJPG);
        $this->assertNotNull($upload->original);

        $path = public_path(str_replace(url('/'), '', $upload->original));

        $upload = Larupload::init('uploader')->delete();

        $this->assertTrue($upload);
        $this->assertFalse(file_exists($path));
    }
}
