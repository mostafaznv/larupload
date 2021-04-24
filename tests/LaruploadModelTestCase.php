<?php

namespace Mostafaznv\Larupload\Test;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Mostafaznv\Larupload\LaruploadEnum;

class LaruploadModelTestCase extends LaruploadTestCase
{
    use LaruploadModelTestCaseTools;

    /*
     * TEST UPLOAD
     */
    public function upload()
    {
        $model = $this->uploadJPG();
        $this->assertNotNull($model->main_file->url());
    }


    /*
     * TEST FILE SIZE
     */
    public function fileSize()
    {
        $model = $this->uploadJPG();
        $this->assertEquals($model->main_file->meta('size'), $this->imageDetails['jpg']['size']);
    }


    /*
     * TEST IMAGE WIDTH AND HEIGHT
     */
    public function imageWidth()
    {
        $model = $this->uploadJPG();
        $this->assertEquals($model->main_file->meta('width'), $this->imageDetails['jpg']['width']);
    }

    public function imageHeight()
    {
        $model = $this->uploadJPG();
        $this->assertEquals($model->main_file->meta('height'), $this->imageDetails['jpg']['height']);
    }


    /*
     * TEST MIME TYPE
     */
    public function mimeType()
    {
        $model = $this->uploadJPG();
        $this->assertEquals($model->main_file->meta('mime_type'), $this->imageDetails['jpg']['mime_type']);
    }

    /*
     * TEST FILE NAME
     */
    public function hashName()
    {
        $model = $this->uploadJPG();
        $this->assertEquals($model->main_file->meta('name'), $this->imageDetails['jpg']['name']['hash']);
    }

    /*
     * TEST IMAGE DIMENSIONS
     */
    public function coverStyleDimensions()
    {
        $model = $this->uploadJPG();
        $image = $this->image($model->main_file->url('cover'));

        $this->assertNotNull($model->main_file->url('cover'));
        $this->assertEquals(500, $image->getSize()->getWidth());
        $this->assertEquals(500, $image->getSize()->getHeight());
    }

    public function smallStyleDimensions()
    {
        $model = $this->uploadJPG();
        $image = $this->image($model->main_file->url('small'));

        $this->assertNotNull($model->main_file->url('small'));
        $this->assertEquals(200, $image->getSize()->getWidth());
        $this->assertEquals(200, $image->getSize()->getHeight());
    }

    public function mediumStyleDimensions()
    {
        $model = $this->uploadJPG();
        $image = $this->image($model->main_file->url('medium'));

        $this->assertNotNull($model->main_file->url('medium'));
        $this->assertEquals(799, $image->getSize()->getWidth());
        $this->assertEquals(587, $image->getSize()->getHeight());
    }

    public function landscapeStyleDimensions()
    {
        $model = $this->uploadJPG();
        $image = $this->image($model->main_file->url('landscape'));

        $this->assertNotNull($model->main_file->url('landscape'));
        $this->assertEquals(399, $image->getSize()->getWidth());
        $this->assertEquals(293, $image->getSize()->getHeight());
    }

    public function portraitStyleDimensions()
    {
        $model = $this->uploadJPG();
        $image = $this->image($model->main_file->url('portrait'));

        $this->assertNotNull($model->main_file->url('portrait'));
        $this->assertEquals(544, $image->getSize()->getWidth());
        $this->assertEquals(399, $image->getSize()->getHeight());
    }

    public function exactStyleDimensions()
    {
        $model = $this->uploadJPG();
        $image = $this->image($model->main_file->url('exact'));

        $this->assertNotNull($model->main_file->url('exact'));
        $this->assertEquals(300, $image->getSize()->getWidth());
        $this->assertEquals(190, $image->getSize()->getHeight());
    }

    public function autoStyleDimensions()
    {
        $model = $this->uploadJPG();
        $image = $this->image($model->main_file->url('auto'));

        $this->assertNotNull($model->main_file->url('auto'));
        $this->assertEquals(300, $image->getSize()->getWidth());
        $this->assertEquals(220, $image->getSize()->getHeight());
    }


    /*
     * TEST DOMINANT COLOR
     */
    public function jpgDominantColor()
    {
        $model = $this->uploadJPG();
        $this->assertEquals($model->main_file->meta('dominant_color'), $this->imageDetails['jpg']['color']);
    }

    public function pngDominantColor()
    {
        $model = $this->uploadPNG();
        $this->assertEquals($model->main_file->meta('dominant_color'), $this->imageDetails['png']['color']);
    }

    public function svgDominantColor()
    {
        Config::set('larupload.image-processing-library', LaruploadEnum::IMAGICK_IMAGE_LIBRARY);

        $this->initModel();
        $model = $this->uploadSVG();

        $this->assertEquals(true, !!preg_match($this->hexRegex, $model->main_file->meta('dominant_color')));
    }


    /*
     * TEST UPDATE/DELETE COVER
     */
    public function updateCover()
    {
        $model = $this->uploadJPG();
        $this->assertEquals($this->imageDetails['jpg']['name']['hash'], $model->main_file->meta('cover'));

        $model->main_file->updateCover($this->imagePNG);
        $model->save();

        $this->assertEquals($this->imageDetails['png']['name']['hash'], $model->main_file->meta('cover'));
    }

    public function deleteCover()
    {
        $model = $this->uploadJPG();
        $this->assertNotNull($model->main_file->url('cover'));
        $this->assertNotNull($model->main_file->meta('cover'));

        $model->main_file->detachCover();
        $model->save();

        $this->assertNull($model->main_file->url('cover'));
        $this->assertNull($model->main_file->meta('cover'));
    }

    /*
     * TEST AUDIO
     */
    public function audio()
    {
        $model = $this->uploadAudio();

        $this->assertEquals($model->main_file->meta('name'), $this->audioDetails['name']);
        $this->assertEquals($model->main_file->meta('size'), $this->audioDetails['size']);
        $this->assertEquals($model->main_file->meta('mime_type'), $this->audioDetails['mime_type']);
        $this->assertEquals($model->main_file->meta('duration'), $this->audioDetails['duration']);
    }


    /*
     * TEST VIDEO STYLES
     */
    public function uploadVideoStyles()
    {
        $model = $this->uploadVideo();

        // cover
        $meta = $this->video($model->main_file->url('cover'));
        $this->assertNotNull($model->main_file->url('cover'));
        $this->assertEquals(500, $meta['width']);
        $this->assertEquals(500, $meta['height']);

        // small
        $meta = $this->video($model->main_file->url('small'));
        $this->assertNotNull($model->main_file->url('small'));
        $this->assertEquals(200, $meta['width']);
        $this->assertEquals(200, $meta['height']);
        $this->assertEquals(5, $meta['duration']);

        // medium
        $meta = $this->video($model->main_file->url('medium'));
        $this->assertNotNull($model->main_file->url('medium'));
        $this->assertEquals(800, $meta['width']);
        $this->assertEquals(458, $meta['height']);
        $this->assertEquals(5, $meta['duration']);

        // landscape
        $meta = $this->video($model->main_file->url('landscape'));
        $this->assertNotNull($model->main_file->url('landscape'));
        $this->assertEquals(400, $meta['width']);
        $this->assertEquals(228, $meta['height']);
        $this->assertEquals(5, $meta['duration']);

        // portrait
        $meta = $this->video($model->main_file->url('portrait'));
        $this->assertNotNull($model->main_file->url('portrait'));
        $this->assertEquals(700, $meta['width']);
        $this->assertEquals(400, $meta['height']);
        $this->assertEquals(5, $meta['duration']);

        // exact
        $meta = $this->video($model->main_file->url('exact'));
        $this->assertNotNull($model->main_file->url('exact'));
        $this->assertEquals(300, $meta['width']);
        $this->assertEquals(172, $meta['height']);
        $this->assertEquals(5, $meta['duration']);

        // auto
        $meta = $this->video($model->main_file->url('auto'));
        $this->assertNotNull($model->main_file->url('auto'));
        $this->assertEquals(300, $meta['width']);
        $this->assertEquals(172, $meta['height']);
        $this->assertEquals(5, $meta['duration']);
    }


    /*
     * TEST STREAM
     */
    public function uploadVideoStream()
    {
        $model = $this->uploadVideo();

        $path = public_path(str_replace(url('/'), '', $model->main_file->url('stream')));
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
    public function uploadByFunction()
    {
        $model = $this->uploadJpgByFunction();

        $this->assertNotNull($model->main_file->url());
        $this->assertEquals($this->imageDetails['jpg']['name']['hash'], $model->main_file->meta('name'));
        $this->assertEquals($this->imageDetails['jpg']['size'], $model->main_file->meta('size'));
        $this->assertEquals($this->imageDetails['jpg']['mime_type'], $model->main_file->meta('mime_type'));
    }


    /*
     * TEST UPLOAD WITH COVER
     */
    public function uploadWithCover()
    {
        $model = $this->uploadPDF(true);
        $this->assertNotNull($model->main_file->url('cover'));
    }


    /*
     * TEST CUSTOM COVER STYLE
     */
    public function customCoverStyle()
    {
        Config::set('larupload.cover-style', [
            'width'  => 200,
            'height' => 150,
            'mode'   => LaruploadEnum::EXACT_STYLE_MODE
        ]);

        $this->initModel();

        $model = $this->uploadJPG();
        $cover = $model->main_file->url('cover');
        $image = $this->image($cover);

        $this->assertNotNull($cover);
        $this->assertEquals(200, $image->getSize()->getWidth());
        $this->assertEquals(150, $image->getSize()->getHeight());
    }


    /*
     * TEST NAMING METHODS
     */
    public function namingMethods()
    {
        Config::set('larupload.lang', 'fa');
        $time = time();

        $this->initModel();
        $this->model->main_file->namingMethod(LaruploadEnum::SLUG_NAMING_METHOD);

        $model = $this->uploadJPG();
        $this->assertEquals(true, Str::contains($model->main_file->meta('name'), $this->imageDetails['jpg']['name']['slug']));

        $model = $this->uploadFaTitledJPG();
        $this->assertEquals(true, Str::contains($model->main_file->meta('name'), $this->imageDetails['jpg-fa']['name']['slug']));


        $this->initModel();
        $this->model->main_file->namingMethod(LaruploadEnum::TIME_NAMING_METHOD);

        $model = $this->uploadFaTitledJPG();
        $this->assertTrue((int)str_replace('.jpg', '', $model->main_file->meta('name')) >= $time);
    }


    /*
     * TEST CAMEL CASE RESPONSE
     */
    public function camelCaseResponse()
    {
        Config::set('larupload.camel-case-response', true);
        $this->initModel();

        $model = $this->uploadJPG();

        $this->assertTrue($model->main_file->meta()->mimeType == $this->imageDetails['jpg']['mime_type']);
    }


    /*
     * TEST FOLDER NAME
     */
    public function kebabCaseFolderName()
    {
        $model = $this->uploadJPG();

        $this->assertTrue(Str::contains($model->main_file->url(), '/main-file/'));
        $this->assertTrue(Str::contains($model->main_file->url('cover'), '/main-file/'));
        $this->assertTrue(Str::contains($model->main_file->url('small_size'), '/small-size/'));
    }


    /*
     * TEST TO-ARRAY TO-JSON
     */
    public function toArray()
    {
        $array = $this->uploadJPG()->toArray();

        $this->assertTrue(isset($array['main_file']));
    }

    public function toJson()
    {
        $json = $this->uploadJPG()->toJson();
        $obj = json_decode($json);

        $this->assertTrue(isset($obj->main_file) and $obj->main_file);
    }


    /*
     * TEST HIDE LARUPLOAD COLUMNS
     */
    public function laruploadColumnsAreHiddenFromToArray()
    {
        Config::set('larupload.hide-table-columns', true);
        $this->initModel();

        $array = $this->uploadJPG()->toArray();

        $this->assertFalse(isset($array['main_file_file_name']));
    }

    public function laruploadColumnsAreVisibleInToArray()
    {
        Config::set('larupload.hide-table-columns', false);
        $this->initModel();

        $array = $this->uploadJPG()->toArray();

        $this->assertTrue(isset($array['main_file_file_name']));
    }


    /*
     * TEST DOWNLOAD FILE
     */
    public function downloadOriginalFile()
    {
        $model = $this->uploadJPG();

        $this->assertTrue($model->main_file->download()->getStatusCode() == 200);
    }

    public function downloadCoverFile()
    {
        $model = $this->uploadJPG();

        $this->assertTrue($model->main_file->download('cover')->getStatusCode() == 200);
    }


    /*
     * TEST DELETE FILE
     */
    public function deleteFileBySettAttribute()
    {
        $model = $this->uploadJPG();
        $this->assertNotNull($model->main_file->url());

        $model->main_file = LARUPLOAD_NULL;
        $model->save();

        $this->assertNull($model->main_file->url());
        $this->assertNull($model->main_file->meta('name'));
    }

    public function deleteFileByDetachFunction()
    {
        $model = $this->uploadJPG();
        $this->assertNotNull($model->main_file->url());

        $model->main_file->detach();
        $model->save();

        $this->assertNull($model->main_file->url());
        $this->assertNull($model->main_file->meta('name'));
    }


    /*
     * TEST DELETE MODEL
     */
    public function deleteModel()
    {
        $model = $this->uploadJPG();
        $styles = $this->allAttachmentPaths($model->main_file);

        $model->delete();

        foreach ($styles as $style) {
            $this->assertFalse(file_exists($style));
        }
    }

    public function deleteModelWithPreserveFiles()
    {
        $this->model->main_file->preserveFiles(true);

        $model = $this->uploadJPG();
        $styles = $this->allAttachmentPaths($model->main_file);

        $model->delete();

        foreach ($styles as $style) {
            $this->assertTrue(file_exists($style));
        }
    }

    public function softDelete()
    {
        $this->initSoftDeleteModel();

        $model = $this->uploadJPG();
        $styles = $this->allAttachmentPaths($model->main_file);

        $model->delete();

        foreach ($styles as $style) {
            $this->assertTrue(file_exists($style));
        }
    }
}
