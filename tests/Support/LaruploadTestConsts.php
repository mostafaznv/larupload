<?php

namespace Mostafaznv\Larupload\Test\Support;

class LaruploadTestConsts
{
    public const HEX_REGEX = '/^#[0-9A-F]{6}$/i';

    public const IMAGE_DETAILS = [
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
                'original' => 'image.jpg',
                'hash'     => '9e55cf595703eaa109025073caed65a4.jpg',
                'slug'     => 'image',
            ]
        ],

        'jpg-fa' => [
            'size'      => 35700,
            'width'     => 1077,
            'height'    => 791,
            'mime_type' => 'image/jpeg',
            'color'     => '#f4c00a',
            'name'      => [
                'original' => 'تیم بارسلونا.jpeg',
                'hash'     => '9e55cf595703eaa109025073caed65a4.jpg',
                'slug'     => 'تیم-بارسلونا',
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

        'webp' => [
            'size'      => 19518,
            'width'     => 1077,
            'height'    => 791,
            'mime_type' => 'image/webp',
            'color'     => '#242e48',
            'name'      => [
                'original' => 'image.webp',
                'hash'     => '1489c881d5033d47aaa7462ec12a6432.webp',
            ]
        ],

        'svg' => [
            'size'      => 7918,
            'width'     => 800,
            'height'    => 810,
            'mime_type' => 'image/svg+xml',
            'color'     => '#e7c004',
            'name'      => [
                'hash' => 'd8ea748a65e63eb9d11efdf6eaf623c5.svg',
            ]
        ],

        'gif' => [
            'size'      => 15860,
            'width'     => 150,
            'height'    => 189,
            'mime_type' => 'image/gif',
            'color'     => '#086e09',
            'name'      => [
                'hash' => '710bf7618c3a942d5c3279ff0bb282c1.gif',
            ]
        ],
    ];

    public const AUDIO_DETAILS = [
        'name'      => 'cd3eb553923c076068f8a7057fcd7113.mp3',
        'size'      => 470173,
        'mime_type' => 'audio/mpeg',
        'duration'  => 67,
    ];

    public const VIDEO_DETAILS = [
        'name'      => 'a3ac7ddabb263c2d00b73e8177d15c8d.mp4',
        'size'      => 383631,
        'mime_type' => 'video/mp4',
        'width'     => 560,
        'height'    => 320,
        'duration'  => 5,
        'color'     => '#754625',
        'format'    => 'mp4',
        'cover'     => 'a3ac7ddabb263c2d00b73e8177d15c8d.jpg'

    ];
}
