<?php

namespace Mostafaznv\Larupload\Test;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class LaruploadModelHeavyTest extends LaruploadTestCase
{
    use LaruploadModelTestCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mode = 'heavy';

        $this->migrate();
        $this->initModel();
        $this->initFiles();
    }

    /*
     * TEST UPLOAD
     */
    public function testUploadJPG()
    {
        $jpg = $this->uploadJPG();
        $this->assertNotNull($jpg->file->original);
    }

    /*
     * TEST FILE SIZE
     */
    public function testJpgFileSize()
    {
        $jpg = $this->uploadJPG();
        $this->assertEquals($jpg->file->meta->size, $this->imageDetails['jpg']['size']);
    }

    /*
     * TEST IMAGE WIDTH AND HEIGHT
     */
    public function testJpgWidth()
    {
        $jpg = $this->uploadJPG();
        $this->assertEquals($jpg->file->meta->width, $this->imageDetails['jpg']['width']);
    }

    public function testJpgHeight()
    {
        $jpg = $this->uploadJPG();
        $this->assertEquals($jpg->file->meta->height, $this->imageDetails['jpg']['height']);
    }


    /*
     * TEST MIME TYPE
     */
    public function testJpgMimeType()
    {
        $jpg = $this->uploadJPG();
        $this->assertEquals($jpg->file->meta->mime_type, $this->imageDetails['jpg']['mime_type']);
    }

    /*
     * TEST FILE NAME
     */
    public function testJpgHashName()
    {
        $jpg = $this->uploadJPG();
        $this->assertEquals($jpg->file->meta->name, $this->imageDetails['jpg']['name']['hash']);
    }

    /*
     * TEST IMAGE SIZE
     */
    public function testUploadCoverJPG()
    {
        $jpg = $this->uploadJPG();
        $image = $this->image($jpg->file->cover);

        $this->assertNotNull($jpg->file->cover);
        $this->assertEquals(500, $image->getSize()->getWidth());
        $this->assertEquals(500, $image->getSize()->getHeight());
    }

    public function testUploadSmallJPG()
    {
        $jpg = $this->uploadJPG();
        $image = $this->image($jpg->file->small);

        $this->assertNotNull($jpg->file->small);
        $this->assertEquals(200, $image->getSize()->getWidth());
        $this->assertEquals(200, $image->getSize()->getHeight());
    }

    public function testUploadMediumJPG()
    {
        $jpg = $this->uploadJPG();
        $image = $this->image($jpg->file->medium);

        $this->assertNotNull($jpg->file->medium);
        $this->assertEquals(799, $image->getSize()->getWidth());
        $this->assertEquals(588, $image->getSize()->getHeight());
    }

    public function testUploadLandscapeJPG()
    {
        $jpg = $this->uploadJPG();
        $image = $this->image($jpg->file->landscape);

        $this->assertNotNull($jpg->file->landscape);
        $this->assertEquals(400, $image->getSize()->getWidth());
        $this->assertEquals(294, $image->getSize()->getHeight());
    }

    public function testUploadPortraitJPG()
    {
        $jpg = $this->uploadJPG();
        $image = $this->image($jpg->file->portrait);

        $this->assertNotNull($jpg->file->portrait);
        $this->assertEquals(545, $image->getSize()->getWidth());
        $this->assertEquals(400, $image->getSize()->getHeight());
    }

    public function testUploadExactJPG()
    {
        $jpg = $this->uploadJPG();
        $image = $this->image($jpg->file->exact);

        $this->assertNotNull($jpg->file->exact);
        $this->assertEquals(300, $image->getSize()->getWidth());
        $this->assertEquals(190, $image->getSize()->getHeight());
    }

    public function testUploadAutoWidthJPG()
    {
        $jpg = $this->uploadJPG();
        $image = $this->image($jpg->file->auto);

        $this->assertNotNull($jpg->file->auto);
        $this->assertEquals(300, $image->getSize()->getWidth());
        $this->assertEquals(220, $image->getSize()->getHeight());
    }

    /*
     * TEST DOMINANT COLOR
     */
    public function testJpgDominantColor()
    {
        $jpg = $this->uploadJPG();
        $this->assertEquals($jpg->file->meta->dominant_color, $this->imageDetails['jpg']['color']);
    }

    public function testPngDominantColor()
    {
        $png = $this->uploadPNG();
        $this->assertEquals($png->file->meta->dominant_color, $this->imageDetails['png']['color']);
    }

    public function testSvgDominantColor()
    {
        Config::set('larupload.image_processing_library', 'Imagine\Imagick\Imagine');

        $this->initModel();
        $svg = $this->uploadSVG();

        $this->assertEquals(true, !!preg_match($this->hexRegex, $svg->file->meta->dominant_color));
    }

    /*
     * TEST AUDIO
     */
    public function testAudio()
    {
        $audio = $this->uploadAudio();

        $this->assertEquals($audio->file->meta->name, $this->audioDetails['name']);
        $this->assertEquals($audio->file->meta->size, $this->audioDetails['size']);
        $this->assertEquals($audio->file->meta->mime_type, $this->audioDetails['mime_type']);
        $this->assertEquals($audio->file->meta->duration, $this->audioDetails['duration']);
    }

    /*
     * TEST VIDEO STYLES
     */
    public function testUploadVideoStyles()
    {
        $video = $this->uploadVideo();

        // cover
        $meta = $this->video($video->file->cover);
        $this->assertNotNull($video->file->cover);
        $this->assertEquals(500, $meta['width']);
        $this->assertEquals(500, $meta['height']);

        // small
        $meta = $this->video($video->file->small);
        $this->assertNotNull($video->file->small);
        $this->assertEquals(200, $meta['width']);
        $this->assertEquals(200, $meta['height']);
        $this->assertEquals(5, $meta['duration']);

        // medium
        $meta = $this->video($video->file->medium);
        $this->assertNotNull($video->file->medium);
        $this->assertEquals(800, $meta['width']);
        $this->assertEquals(458, $meta['height']);
        $this->assertEquals(5, $meta['duration']);

        // landscape
        $meta = $this->video($video->file->landscape);
        $this->assertNotNull($video->file->landscape);
        $this->assertEquals(400, $meta['width']);
        $this->assertEquals(228, $meta['height']);
        $this->assertEquals(5, $meta['duration']);

        // portrait
        $meta = $this->video($video->file->portrait);
        $this->assertNotNull($video->file->portrait);
        $this->assertEquals(700, $meta['width']);
        $this->assertEquals(400, $meta['height']);
        $this->assertEquals(5, $meta['duration']);

        // exact
        $meta = $this->video($video->file->exact);
        $this->assertNotNull($video->file->exact);
        $this->assertEquals(300, $meta['width']);
        $this->assertEquals(172, $meta['height']);
        $this->assertEquals(5, $meta['duration']);

        // auto
        $meta = $this->video($video->file->auto);
        $this->assertNotNull($video->file->auto);
        $this->assertEquals(300, $meta['width']);
        $this->assertEquals(172, $meta['height']);
        $this->assertEquals(5, $meta['duration']);
    }

    /*
     * TEST STREAM
     */
    public function testUploadStream()
    {
        $video = $this->uploadVideo();

        $path = public_path(str_replace(url('/'), '', $video->file->stream));
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
     * TEST UPLOAD BY FUNCTION
     */
    public function testUploadByFunction()
    {
        $jpg = $this->uploadJPG();

        $this->assertNotNull($jpg->file->original);
        $this->assertEquals($this->imageDetails['jpg']['name']['hash'], $jpg->file->meta->name);
        $this->assertEquals($this->imageDetails['jpg']['size'], $jpg->file->meta->size);
        $this->assertEquals($this->imageDetails['jpg']['mime_type'], $jpg->file->meta->mime_type);
    }

    /*
     * TEST UPLOAD WITH COVER
     */
    public function testUploadWithCover()
    {
        $pdf = $this->uploadPDF(true);
        $this->assertNotNull($pdf->file->cover);
    }

    /*
     * TEST CUSTOM COVER STYLE
     */
    public function testCustomCoverStyle()
    {
        $this->initModel([
            'cover_style' => [
                'width'  => 200,
                'height' => 150,
                'mode'   => 'exact'
            ]
        ]);
        $jpg = $this->uploadJPG();
        $image = $this->image($jpg->file->cover);


        $this->assertNotNull($jpg->file->cover);
        $this->assertEquals(200, $image->getSize()->getWidth());
        $this->assertEquals(150, $image->getSize()->getHeight());
    }

    /*
     * TEST NAMING METHODS
     */
    public function testNamingMethods()
    {
        $time = time();

        Config::set('larupload.lang', 'fa');
        $this->initModel(['naming_method' => 'slug']);

        $jpg = $this->uploadJPG();
        $this->assertEquals(true,  Str::contains($jpg->file->meta->name, $this->imageDetails['jpg']['name']['slug']));


        $jpg = $this->uploadFaTitledJPG();
        $this->assertEquals(true, Str::contains($jpg->file->meta->name, $this->imageDetails['jpg-fa']['name']['slug']));

        $this->initModel(['naming_method' => 'time']);
        $jpg = $this->uploadFaTitledJPG();

        $this->assertTrue((int)str_replace('.jpg', '', $jpg->file->meta->name) >= $time);
    }

    /*
     * TEST DELETE FILE
     */
    public function testDeleteFile()
    {
        $jpg = $this->uploadJPG();

        $this->assertNotNull($jpg->file->original);

        $jpg->file = LARUPLOAD_NULL;
        $jpg->save();

        $this->assertNull($jpg->file->original);
        $this->assertNull($jpg->file->meta->name);
    }
}
