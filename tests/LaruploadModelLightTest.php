<?php

namespace Mostafaznv\Larupload\Test;

use Mostafaznv\Larupload\LaruploadEnum;

class LaruploadModelLightTest extends LaruploadModelTestCase
{
    use LaruploadModelTestCaseTools;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mode = LaruploadEnum::LIGHT_MODE;

        $this->migrate();
        $this->initModel();
        $this->initFiles();
    }

    /*
     * TEST UPLOAD
     */
    public function testUpload()
    {
        self::upload();
    }


    /*
     * TEST FILE SIZE
     */
    public function testFileSize()
    {
        self::fileSize();
    }


    /*
     * TEST IMAGE WIDTH AND HEIGHT
     */
    public function testImageWidth()
    {
        self::imageWidth();
    }

    public function testImageHeight()
    {
        self::imageHeight();
    }


    /*
     * TEST MIME TYPE
     */
    public function testJpgMimeType()
    {
        self::mimeType();
    }


    /*
     * TEST FILE NAME
     */
    public function testHashName()
    {
        self::hashName();
    }


    /*
     * TEST IMAGE DIMENSIONS
     */
    public function testCoverStyleDimensions()
    {
        self::coverStyleDimensions();
    }

    public function testSmallStyleDimensions()
    {
        self::smallStyleDimensions();
    }

    public function testMediumStyleDimensions()
    {
        self::mediumStyleDimensions();
    }

    public function testLandscapeStyleDimensions()
    {
        self::landscapeStyleDimensions();
    }

    public function testPortraitStyleDimensions()
    {
        self::portraitStyleDimensions();
    }

    public function testExactStyleDimensions()
    {
        self::exactStyleDimensions();
    }

    public function testAutoStyleDimensions()
    {
        self::autoStyleDimensions();
    }


    /*
     * TEST DOMINANT COLOR
     */
    public function testJpgDominantColor()
    {
        self::jpgDominantColor();
    }

    public function testPngDominantColor()
    {
        self::pngDominantColor();
    }

    public function testSvgDominantColor()
    {
        self::pngDominantColor();
    }


    /*
     * TEST UPDATE/DELETE COVER
     */
    public function testUpdateCover()
    {
        self::updateCover();
    }

    public function testDeleteCover()
    {
        self::deleteCover();
    }


    /*
     * TEST AUDIO
     */
    public function testAudio()
    {
        self::audio();
    }


    /*
     * TEST VIDEO STYLES
     */
    public function testVideoStyles()
    {
        self::uploadVideoStyles();
    }


    /*
     * TEST STREAM
     */
    public function testUploadVideoStream()
    {
        self::uploadVideoStream();
    }


    /*
     * TEST UPLOAD BY FUNCTION
     */
    public function testUploadByFunction()
    {
        self::uploadByFunction();
    }


    /*
     * TEST UPLOAD WITH COVER
     */
    public function testUploadWithCover()
    {
        self::uploadWithCover();
    }


    /*
     * TEST CUSTOM COVER STYLE
     */
    public function testCustomCoverStyle()
    {
        self::customCoverStyle();
    }


    /*
     * TEST NAMING METHODS
     */
    public function testNamingMethods()
    {
        self::namingMethods();
    }


    /*
     * TEST CAMEL CASE RESPONSE
     */
    public function testCamelCaseResponse()
    {
        self::camelCaseResponse();
    }


    /*
     * TEST FOLDER NAME
     */
    public function testKebabCaseFolderName()
    {
        self::kebabCaseFolderName();
    }


    /*
     * TEST TO-ARRAY TO-JSON
     */
    public function testToArray()
    {
        self::toArray();
    }

    public function testToJson()
    {
        self::toJson();
    }


    /*
     * TEST HIDE LARUPLOAD COLUMNS
     */
    public function testLaruploadColumnsAreHiddenFromToArray()
    {
        self::laruploadColumnsAreHiddenFromToArray();
    }

    public function testLaruploadColumnsAreVisibleInToArray()
    {
        self::laruploadColumnsAreVisibleInToArray();
    }


    /*
     * TEST DOWNLOAD FILE
     */
    public function testDownloadOriginalFile()
    {
        self::downloadOriginalFile();
    }

    public function testDownloadCoverFile()
    {
        self::downloadCoverFile();
    }


    /*
     * TEST DELETE FILE
     */
    public function testDeleteFileBySettAttribute()
    {
        self::deleteFileBySettAttribute();
    }

    public function testDeleteFileByDetachFunction()
    {
        self::deleteFileByDetachFunction();
    }


    /*
     * TEST DELETE MODEL
     */
    public function testDeleteModel()
    {
        self::deleteModel();
    }

    public function testDeleteModelWithPreserveFiles()
    {
        self::deleteModelWithPreserveFiles();
    }

    public function testSoftDelete()
    {
        self::softDelete();
    }
}
