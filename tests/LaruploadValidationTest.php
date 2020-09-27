<?php

namespace Mostafaznv\Larupload\Test;

use Mostafaznv\Larupload\Helpers\Helper;

class LaruploadValidationTest extends LaruploadTestCase
{
    private function showErrors(array $errors): string
    {
        $err = [];

        foreach ($errors as $error) {
            foreach ($error as $item) {
                $err[] = $item;
            }
        }

        return implode(PHP_EOL, array_values($err));
    }

    // mode
    public function testMode()
    {
        $errors = Helper::validate(['mode' => 'light']);
        $this->assertCount(0, $errors, $this->showErrors($errors));

        $errors = Helper::validate(['mode' => 'heavy']);
        $this->assertCount(0, $errors, $this->showErrors($errors));

        $errors = Helper::validate(['mode' => 'pro']);
        $this->assertCount(1, $errors, $this->showErrors($errors));
    }

    // naming method
    public function testNamingMethod()
    {
        $errors = Helper::validate(['naming_method' => 'slug']);
        $this->assertCount(0, $errors, $this->showErrors($errors));

        $errors = Helper::validate(['naming_method' => 'hash_file']);
        $this->assertCount(0, $errors, $this->showErrors($errors));

        $errors = Helper::validate(['naming_method' => 'time']);
        $this->assertCount(0, $errors, $this->showErrors($errors));

        $errors = Helper::validate(['naming_method' => 'pascal-case']);
        $this->assertCount(1, $errors, $this->showErrors($errors));
    }

    // with meta
    public function testWithMetaIsBoolean()
    {
        $errors = Helper::validate(['with_meta' => true]);
        $this->assertCount(0, $errors, $this->showErrors($errors));

        $errors = Helper::validate(['with_meta' => false]);
        $this->assertCount(0, $errors, $this->showErrors($errors));

        $errors = Helper::validate(['with_meta' => 1]);
        $this->assertCount(0, $errors, $this->showErrors($errors));

        $errors = Helper::validate(['with_meta' => 0]);
        $this->assertCount(0, $errors, $this->showErrors($errors));

        $errors = Helper::validate(['with_meta' => 'yes']);
        $this->assertCount(1, $errors, $this->showErrors($errors));
    }

    // style
    public function testStyleShouldNotEmpty()
    {
        $errors = Helper::validate(['styles' => []]);
        $this->assertCount(1, $errors, $this->showErrors($errors));

        $errors = Helper::validate(['styles' => [[]]]);
        $this->assertCount(1, $errors, $this->showErrors($errors));
    }

    public function testStyleNameShouldBeString()
    {
        $errors = Helper::validate(['styles' => ['style-name' => ['height' => 500]]]);
        $this->assertCount(0, $errors, $this->showErrors($errors));

        $errors = Helper::validate(['styles' => ['2' => ['height' => 500]]]);
        $this->assertCount(1, $errors, $this->showErrors($errors));

        $errors = Helper::validate(['styles' => [['height' => 500]]]);
        $this->assertCount(1, $errors, $this->showErrors($errors));
    }

    public function testStyleHeightShouldBeNumeric()
    {
        $errors = Helper::validate(['styles' => ['style-name' => ['height' => 500]]]);
        $this->assertCount(0, $errors, $this->showErrors($errors));

        $errors = Helper::validate(['styles' => ['style-name' => ['height' => 'string']]]);
        $this->assertCount(1, $errors, $this->showErrors($errors));
    }

    public function testStyleWidthShouldBeNumeric()
    {
        $errors = Helper::validate(['styles' => ['style-name' => ['width' => 500]]]);
        $this->assertCount(0, $errors, $this->showErrors($errors));

        $errors = Helper::validate(['styles' => ['style-name' => ['width' => 'string']]]);
        $this->assertCount(1, $errors, $this->showErrors($errors));
    }

    public function testStyleModeShouldAcceptLandscape()
    {
        $errors = Helper::validate(['styles' => ['style-name' => ['mode' => 'landscape']]]);
        $this->assertCount(0, $errors, $this->showErrors($errors));
    }

    public function testStyleModeShouldAcceptPortrait()
    {
        $errors = Helper::validate(['styles' => ['style-name' => ['mode' => 'portrait']]]);
        $this->assertCount(0, $errors, $this->showErrors($errors));
    }

    public function testStyleModeShouldAcceptCrop()
    {
        // without width
        $errors = Helper::validate(['styles' => ['style-name' => ['mode' => 'crop', 'height' => 500]]]);
        $this->assertCount(1, $errors, $this->showErrors($errors));

        // without height
        $errors = Helper::validate(['styles' => ['style-name' => ['mode' => 'crop', 'width' => 500]]]);
        $this->assertCount(1, $errors, $this->showErrors($errors));

        // with height and width
        $errors = Helper::validate(['styles' => ['style-name' => ['mode' => 'crop', 'height' => 500, 'width' => 500]]]);
        $this->assertCount(0, $errors, $this->showErrors($errors));
    }

    public function testStyleModeShouldAcceptExact()
    {
        $errors = Helper::validate(['styles' => ['style-name' => ['mode' => 'exact']]]);
        $this->assertCount(0, $errors, $this->showErrors($errors));
    }

    public function testStyleModeShouldAcceptAuto()
    {
        $errors = Helper::validate(['styles' => ['style-name' => ['mode' => 'auto']]]);
        $this->assertCount(0, $errors, $this->showErrors($errors));
    }

    public function testStyleModeShouldNotAcceptWrongValue()
    {
        $errors = Helper::validate(['styles' => ['style-name' => ['mode' => 'free']]]);
        $this->assertCount(1, $errors, $this->showErrors($errors));
    }

    public function testStyleTypeShouldBeArray()
    {
        $errors = Helper::validate(['styles' => ['style-name' => ['type' => 'image']]]);
        $this->assertCount(1, $errors, $this->showErrors($errors));
    }

    public function testStyleTypeShouldAcceptImage()
    {
        $errors = Helper::validate(['styles' => ['style-name' => ['type' => ['image']]]]);
        $this->assertCount(0, $errors, $this->showErrors($errors));
    }

    public function testStyleTypeShouldAcceptVideo()
    {
        $errors = Helper::validate(['styles' => ['style-name' => ['type' => ['video']]]]);
        $this->assertCount(0, $errors, $this->showErrors($errors));
    }

    public function testStyleTypeShouldAcceptImageAndVideo()
    {
        $errors = Helper::validate(['styles' => ['style-name' => ['type' => ['video', 'image']]]]);
        $this->assertCount(0, $errors, $this->showErrors($errors));
    }

    public function testStyleTypeShouldNotAcceptWrongValue()
    {
        $errors = Helper::validate(['styles' => ['style-name' => ['type' => ['image', 'pdf']]]]);
        $this->assertCount(1, $errors, $this->showErrors($errors));
    }

    // stream
    public function testStreamHeightShouldNotEmpty()
    {
        $errors = Helper::validate(['styles' => ['stream' => []]]);
        $this->assertCount(1, $errors, $this->showErrors($errors));
    }

    public function testStreamNameShouldBeString()
    {
        $errors = Helper::validate([
            'styles' => [
                'stream' => [
                    '480p' => [
                        'width'   => 640,
                        'height'  => 480,
                        'bitrate' => [
                            'audio' => '64k',
                            'video' => '300000'
                        ]
                    ],
                ]
            ]
        ]);
        $this->assertCount(0, $errors, $this->showErrors($errors));

        $errors = Helper::validate([
            'styles' => [
                'stream' => [
                    '2' => [
                        'width'   => 640,
                        'height'  => 480,
                        'bitrate' => [
                            'audio' => '64k',
                            'video' => '300000'
                        ]
                    ],
                ]
            ]
        ]);
        $this->assertCount(1, $errors, $this->showErrors($errors));

        $errors = Helper::validate([
            'styles' => [
                'stream' => [
                    [
                        'width'   => 640,
                        'height'  => 480,
                        'bitrate' => [
                            'audio' => '64k',
                            'video' => '300000'
                        ]
                    ],
                ]
            ]
        ]);
        $this->assertCount(1, $errors, $this->showErrors($errors));
    }

    public function testStreamHeightIsRequiredNumeric()
    {
        $errors = Helper::validate([
            'styles' => [
                'stream' => [
                    '480p' => [
                        'width'   => 640,
                        'bitrate' => [
                            'audio' => '64k',
                            'video' => '300000'
                        ]
                    ],
                ]
            ]
        ]);
        $this->assertCount(1, $errors, $this->showErrors($errors));

        $errors = Helper::validate([
            'styles' => [
                'stream' => [
                    '480p' => [
                        'height'  => 'vertical',
                        'width'   => 640,
                        'bitrate' => [
                            'audio' => '64k',
                            'video' => '300000'
                        ]
                    ],
                ]
            ]
        ]);
        $this->assertCount(1, $errors, $this->showErrors($errors));
    }

    public function testStreamWidthIsRequiredNumeric()
    {
        $errors = Helper::validate([
            'styles' => [
                'stream' => [
                    '480p' => [
                        'height'  => 640,
                        'bitrate' => [
                            'audio' => '64k',
                            'video' => '300000'
                        ]
                    ],
                ]
            ]
        ]);
        $this->assertCount(1, $errors, $this->showErrors($errors));

        $errors = Helper::validate([
            'styles' => [
                'stream' => [
                    '480p' => [
                        'width'   => 'wide',
                        'height'  => 640,
                        'bitrate' => [
                            'audio' => '64k',
                            'video' => '300000'
                        ]
                    ],
                ]
            ]
        ]);
        $this->assertCount(1, $errors, $this->showErrors($errors));
    }

    public function testStreamBitrateIsRequiredNumeric()
    {
        $errors = Helper::validate([
            'styles' => [
                'stream' => [
                    '480p' => [
                        'width'   => 640,
                        'height'  => 480,
                        'bitrate' => [
                            'video' => '300000',
                        ]
                    ],
                ]
            ]
        ]);
        $this->assertCount(1, $errors, $this->showErrors($errors));

        $errors = Helper::validate([
            'styles' => [
                'stream' => [
                    '480p' => [
                        'width'   => 640,
                        'height'  => 480,
                        'bitrate' => [
                            'audio' => '64k',
                        ]
                    ],
                ]
            ]
        ]);
        $this->assertCount(1, $errors, $this->showErrors($errors));

        $errors = Helper::validate([
            'styles' => [
                'stream' => [
                    '480p' => [
                        'width'   => 640,
                        'height'  => 480,
                        'bitrate' => [
                            'audio' => 'mute',
                            'video' => '300000'
                        ]
                    ],
                ]
            ]
        ]);
        $this->assertCount(1, $errors, $this->showErrors($errors));

        $errors = Helper::validate([
            'styles' => [
                'stream' => [
                    '480p' => [
                        'width'   => 640,
                        'height'  => 480,
                        'bitrate' => [
                            'audio' => '64k',
                            'video' => 'hq'
                        ]
                    ],
                ]
            ]
        ]);
        $this->assertCount(1, $errors, $this->showErrors($errors));
    }

    // style full example
    public function testStyleFullExample()
    {
        $errors = Helper::validate([
            'styles' => [
                'thumbnail' => [
                    'height' => 500,
                    'width'  => 500,
                    'mode'   => 'crop',
                    'type'   => ['image', 'video'],
                ],

                'medium' => [
                    'height' => 1000,
                    'width'  => 1000,
                    'mode'   => 'auto',
                    'type'   => ['image']
                ],

                'stream' => [
                    '480p' => [
                        'width'   => 640,
                        'height'  => 480,
                        'bitrate' => [
                            'audio' => '64k',
                            'video' => '300000'
                        ]
                    ],

                    '720p' => [
                        'width'   => 1280,
                        'height'  => 720,
                        'bitrate' => [
                            'audio' => '64k',
                            'video' => '400000'
                        ]
                    ],

                    '1080p' => [
                        'width'   => 1920,
                        'height'  => 1080,
                        'bitrate' => [
                            'audio' => '64k',
                            'video' => '500000'
                        ]
                    ]
                ]
            ]
        ]);

        $this->assertCount(0, $errors, $this->showErrors($errors));
    }

    // dominant color
    public function testDominantColorIsBoolean()
    {
        $errors = Helper::validate(['dominant_color' => true]);
        $this->assertCount(0, $errors, $this->showErrors($errors));

        $errors = Helper::validate(['dominant_color' => false]);
        $this->assertCount(0, $errors, $this->showErrors($errors));

        $errors = Helper::validate(['dominant_color' => 1]);
        $this->assertCount(0, $errors, $this->showErrors($errors));

        $errors = Helper::validate(['dominant_color' => 0]);
        $this->assertCount(0, $errors, $this->showErrors($errors));

        $errors = Helper::validate(['dominant_color' => 'rgba']);
        $this->assertCount(1, $errors, $this->showErrors($errors));
    }

    // generate cover
    public function testGenerateColorIsBoolean()
    {
        $errors = Helper::validate(['generate_cover' => true]);
        $this->assertCount(0, $errors, $this->showErrors($errors));

        $errors = Helper::validate(['generate_cover' => false]);
        $this->assertCount(0, $errors, $this->showErrors($errors));

        $errors = Helper::validate(['generate_cover' => 1]);
        $this->assertCount(0, $errors, $this->showErrors($errors));

        $errors = Helper::validate(['generate_cover' => 0]);
        $this->assertCount(0, $errors, $this->showErrors($errors));

        $errors = Helper::validate(['generate_cover' => 'no-cover']);
        $this->assertCount(1, $errors, $this->showErrors($errors));
    }

    // cover style
    public function testCoverStyleHeightShouldBeNumeric()
    {
        $errors = Helper::validate(['cover_style' => ['height' => 500]]);
        $this->assertCount(0, $errors, $this->showErrors($errors));

        $errors = Helper::validate(['cover_style' => ['height' => 'large']]);
        $this->assertCount(1, $errors, $this->showErrors($errors));
    }

    public function testCoverStyleWidthShouldBeNumeric()
    {
        $errors = Helper::validate(['cover_style' => ['width' => 500]]);
        $this->assertCount(0, $errors, $this->showErrors($errors));

        $errors = Helper::validate(['cover_style' => ['width' => 'wide']]);
        $this->assertCount(1, $errors, $this->showErrors($errors));
    }

    public function testCoverStyleModeShouldAcceptLandscape()
    {
        $errors = Helper::validate(['cover_style' => ['mode' => 'landscape']]);
        $this->assertCount(0, $errors, $this->showErrors($errors));
    }

    public function testCoverStyleModeShouldAcceptPortrait()
    {
        $errors = Helper::validate(['cover_style' => ['mode' => 'portrait']]);
        $this->assertCount(0, $errors, $this->showErrors($errors));
    }

    public function testCoverStyleModeShouldAcceptCrop()
    {
        // without width
        $errors = Helper::validate(['cover_style' => ['mode' => 'crop', 'height' => 500]]);
        $this->assertCount(1, $errors, $this->showErrors($errors));

        // without height
        $errors = Helper::validate(['cover_style' => ['mode' => 'crop', 'width' => 500]]);
        $this->assertCount(1, $errors, $this->showErrors($errors));

        // with height and width
        $errors = Helper::validate(['cover_style' => ['mode' => 'crop', 'height' => 500, 'width' => 500]]);
        $this->assertCount(0, $errors, $this->showErrors($errors));
    }

    public function testCoverStyleModeShouldAcceptExact()
    {
        $errors = Helper::validate(['cover_style' => ['mode' => 'exact']]);
        $this->assertCount(0, $errors, $this->showErrors($errors));
    }

    public function testCoverStyleModeShouldAcceptAuto()
    {
        $errors = Helper::validate(['cover_style' => ['mode' => 'auto']]);
        $this->assertCount(0, $errors, $this->showErrors($errors));
    }

    public function testCoverStyleModeShouldNotAcceptWrongValue()
    {
        $errors = Helper::validate(['cover_style' => ['mode' => 'expert']]);
        $this->assertCount(1, $errors, $this->showErrors($errors));
    }

    // keep old files
    public function testKeepOldFilesIsBoolean()
    {
        $errors = Helper::validate(['keep_old_files' => true]);
        $this->assertCount(0, $errors, $this->showErrors($errors));

        $errors = Helper::validate(['keep_old_files' => false]);
        $this->assertCount(0, $errors, $this->showErrors($errors));

        $errors = Helper::validate(['keep_old_files' => 1]);
        $this->assertCount(0, $errors, $this->showErrors($errors));

        $errors = Helper::validate(['keep_old_files' => 0]);
        $this->assertCount(0, $errors, $this->showErrors($errors));

        $errors = Helper::validate(['keep_old_files' => 'just-new']);
        $this->assertCount(1, $errors, $this->showErrors($errors));
    }

    // preserve files
    public function testPreserveFilesIsBoolean()
    {
        $errors = Helper::validate(['preserve_files' => true]);
        $this->assertCount(0, $errors, $this->showErrors($errors));

        $errors = Helper::validate(['preserve_files' => false]);
        $this->assertCount(0, $errors, $this->showErrors($errors));

        $errors = Helper::validate(['preserve_files' => 1]);
        $this->assertCount(0, $errors, $this->showErrors($errors));

        $errors = Helper::validate(['preserve_files' => 0]);
        $this->assertCount(0, $errors, $this->showErrors($errors));

        $errors = Helper::validate(['preserve_files' => 'files']);
        $this->assertCount(1, $errors, $this->showErrors($errors));
    }

    // allowed mime types
    public function testAllowedMimeTypesIsArray()
    {
        $errors = Helper::validate(['allowed_mime_types' => ['video/mp4']]);
        $this->assertCount(0, $errors, $this->showErrors($errors));

        $errors = Helper::validate(['allowed_mime_types' => 'video/mp4']);
        $this->assertCount(1, $errors, $this->showErrors($errors));
    }

    // allowed mimes
    public function testAllowedMimesIsArray()
    {
        $errors = Helper::validate(['allowed_mimes' => ['mp4', 'mp3']]);
        $this->assertCount(0, $errors, $this->showErrors($errors));

        $errors = Helper::validate(['allowed_mime_types' => 'mp4']);
        $this->assertCount(1, $errors, $this->showErrors($errors));
    }

    // ffmpeg capture frame
    public function testFfmpegCaptureFrameIsNumeric()
    {
        $errors = Helper::validate(['ffmpeg-capture-frame' => 0]);
        $this->assertCount(0, $errors, $this->showErrors($errors));

        $errors = Helper::validate(['ffmpeg-capture-frame' => 1232]);
        $this->assertCount(0, $errors, $this->showErrors($errors));

        $errors = Helper::validate(['ffmpeg-capture-frame' => 123.1]);
        $this->assertCount(0, $errors, $this->showErrors($errors));

        $errors = Helper::validate(['ffmpeg-capture-frame' => -1]);
        $this->assertCount(1, $errors, $this->showErrors($errors));

        $errors = Helper::validate(['ffmpeg-capture-frame' => 999999999999999999]);
        $this->assertCount(1, $errors, $this->showErrors($errors));

        $errors = Helper::validate(['ffmpeg-capture-frame' => 'HC01']);
        $this->assertCount(1, $errors, $this->showErrors($errors));

    }

    // ffmpeg timeout
    public function testFfmpegTimeoutIsNumeric()
    {
        $errors = Helper::validate(['ffmpeg-timeout' => 0]);
        $this->assertCount(0, $errors, $this->showErrors($errors));

        $errors = Helper::validate(['ffmpeg-timeout' => 1232]);
        $this->assertCount(0, $errors, $this->showErrors($errors));

        $errors = Helper::validate(['ffmpeg-timeout' => -1]);
        $this->assertCount(1, $errors, $this->showErrors($errors));

        $errors = Helper::validate(['ffmpeg-timeout' => 'TM3801']);
        $this->assertCount(1, $errors, $this->showErrors($errors));

    }

    // ffmpeg queue
    public function testFfmpegQueueIsBoolean()
    {
        $errors = Helper::validate(['ffmpeg-queue' => true]);
        $this->assertCount(0, $errors, $this->showErrors($errors));

        $errors = Helper::validate(['ffmpeg-queue' => false]);
        $this->assertCount(0, $errors, $this->showErrors($errors));

        $errors = Helper::validate(['ffmpeg-queue' => 1]);
        $this->assertCount(0, $errors, $this->showErrors($errors));

        $errors = Helper::validate(['ffmpeg-queue' => 0]);
        $this->assertCount(0, $errors, $this->showErrors($errors));

        $errors = Helper::validate(['ffmpeg-queue' => 'yes']);
        $this->assertCount(1, $errors, $this->showErrors($errors));
    }

    // ffmpeg max queue num
    public function testFfmpegMaxQueueNumIsNumeric()
    {
        $errors = Helper::validate(['ffmpeg-max-queue-num' => 0]);
        $this->assertCount(1, $errors, $this->showErrors($errors));

        $errors = Helper::validate(['ffmpeg-max-queue-num' => 1232]);
        $this->assertCount(0, $errors, $this->showErrors($errors));

        $errors = Helper::validate(['ffmpeg-max-queue-num' => -1]);
        $this->assertCount(1, $errors, $this->showErrors($errors));

        $errors = Helper::validate(['ffmpeg-max-queue-num' => 'large-number']);
        $this->assertCount(1, $errors, $this->showErrors($errors));

    }
}
