<?php

namespace Mostafaznv\Larupload\Test\Models;

use Illuminate\Database\Eloquent\Model;
use Mostafaznv\Larupload\Traits\Larupload;

class LaruploadUploadHeavy extends Model
{
    use Larupload;

    protected $table   = 'upload_heavy';
    protected $appends = ['file'];

    public function __construct(array $config = [])
    {
        parent::__construct([]);

        $baseConfig = [
            'storage'       => 'local',
            'mode'          => 'heavy',
            'naming_method' => 'hash_file',
            'with_meta'     => true,
            'styles'        => [
                'small' => [
                    'width'  => 200,
                    'height' => 200,
                    'mode'   => 'crop',
                    'type'   => ['image', 'video']
                ],

                'medium' => [
                    'width'  => 800,
                    'height' => 800,
                    'mode'   => 'auto',
                    'type'   => ['image', 'video']
                ],

                'landscape' => [
                    'width' => 400,
                    'mode'  => 'landscape',
                    'type'  => ['image', 'video']
                ],

                'portrait' => [
                    'height' => 400,
                    'mode'   => 'portrait',
                    'type'   => ['image', 'video']
                ],

                'exact' => [
                    'width'  => 300,
                    'height' => 190,
                    'mode'   => 'exact',
                    'type'   => ['image', 'video']
                ],

                'auto' => [
                    'width'  => 300,
                    'height' => 190,
                    'mode'   => 'auto',
                    'type'   => ['image', 'video']
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
                    ]
                ]
            ]
        ];

        if (count($config)) {
            $baseConfig = array_merge($baseConfig, $config);
        }

        $this->hasUploadFile('file', $baseConfig);
    }

    public function getFileAttribute()
    {
        $file = $this->getFiles('file');
        $file->meta = $this->meta('file');

        return $file;
    }
}
