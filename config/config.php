<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Storage Disk
    |--------------------------------------------------------------------------
    |
    | The default disk for handling file storage. Larupload will use available
    | disks in config/filesystems.php
    |
    */

    'disk' => 'local',

    /*
    |--------------------------------------------------------------------------
    | Storage Local Disk
    |--------------------------------------------------------------------------
    |
    | Larupload needs to know your local disk name. when your default disk uses
    | external drivers like sftp, for some reasons larupload needs to use local
    | disk too.
    | notice: in most cases, your local disk and default one are the same
    |
    */

    'local-disk' => 'local',

    /*
    |--------------------------------------------------------------------------
    | Mode
    |--------------------------------------------------------------------------
    |
    | Larupload work with two modes, light and heavy! in light mode the trait store
    | file name in database and metadata in a json column named as meta.
    | But in heavy mode, it stores more columns.
    |
    | Example: light, heavy
    |
    */

    'mode' => Mostafaznv\Larupload\Enums\LaruploadMode::HEAVY,

    /*
    |--------------------------------------------------------------------------
    | With Meta
    |--------------------------------------------------------------------------
    |
    | With set this value true, meta details will return whenever you retrieve urls
    |
    | Example: true, false
    |
    */

    'with-meta' => true,

    /*
    |--------------------------------------------------------------------------
    | Camel Case Response
    |--------------------------------------------------------------------------
    |
    | By default, larupload returns all meta keys in snake_case style. with enabling this option, we return them cameCase
    |
    | Example: true, false
    |
    */

    'camel-case-response' => false,

    /*
    |--------------------------------------------------------------------------
    | Hide Table Columns
    |--------------------------------------------------------------------------
    |
    | Larupload creates multiple columns to work with them. these columns are
    | useless in application-level and even api-level.
    | by default, larupload will hide them from toArray and toJson.
    |
    | Example: true, false
    |
    */

    'hide-table-columns' => true,

    /*
    |--------------------------------------------------------------------------
    | Naming Method
    |--------------------------------------------------------------------------
    |
    | Larupload uses some different methods to generate file name
    |
    | Example: slug, hash_file, time
    | Note: Larupload appends an increment number to end of slug to prevent caching for different files with same name
    |
    */

    'naming-method' => Mostafaznv\Larupload\Enums\LaruploadNamingMethod::SLUG,
    'lang'          => '',

    /*
    |--------------------------------------------------------------------------
    | Image Processing Library
    |--------------------------------------------------------------------------
    |
    | Larupload can resize or crop image files with power of imagine\imagine
    | library.
    |
    | Example: Imagine\Gd\Imagine, Imagine\Imagick\Imagine, or Imagine\Gmagick\Imagine.
    |
    */

    'image-processing-library' => Mostafaznv\Larupload\Enums\LaruploadImageLibrary::GD,

    /*
    |--------------------------------------------------------------------------
    | Cover Flag
    |--------------------------------------------------------------------------
    |
    | Larupload will generate a cover image from video/image if cover flag is true.
    |
    | Example: true, false
    |
    */

    'generate-cover' => true,

    /*
    |--------------------------------------------------------------------------
    | Cover Style
    |--------------------------------------------------------------------------
    |
    | Larupload will generate a cover image from video/image if cover flag is true.
    | Trait will store cover data in cover_file_name, cover_file_size and cover_file_content
    |
    | Note: cover only work in detailed mode
    |
    */

    'cover-style' => Mostafaznv\Larupload\DTOs\Style\ImageStyle::make(
        name: 'cover',
        width: 500,
        height: 500,
        mode: \Mostafaznv\Larupload\Enums\LaruploadMediaStyle::CROP
    ),

    /*
    |--------------------------------------------------------------------------
    | Dominant Color
    |--------------------------------------------------------------------------
    |
    | You can get dominant color from images and videos with this option.
    |
    */

    'dominant-color' => true,

    /*
    |--------------------------------------------------------------------------
    | Dominant Color Quality
    |--------------------------------------------------------------------------
    |
    | You can set quality to adjust the calculation accuracy of the dominant color.
    | 1 is the highest quality settings, 10 is the default. But be aware that there is a trade-off between
    | quality and speed/memory consumption!
    | If the quality settings are too high (close to 1) relative to the image size (pixel counts), it may exceed
    | the memory limit set in the PHP configuration (and computation will be slow).
    |
    */

    'dominant-color-quality' => 10,

    /*
    |--------------------------------------------------------------------------
    | Keep Old Files Flag
    |--------------------------------------------------------------------------
    |
    | Set this to true in order to prevent older file uploads from being deleted
    | from storage when a record is updated with a new upload.
    |
    */

    'keep-old-files' => false,

    /*
    |--------------------------------------------------------------------------
    | Preserve Files Flag
    |--------------------------------------------------------------------------
    |
    | Set this to true in order to prevent file uploads from being deleted
    | from the file system when an attachment is destroyed.  Essentially this
    | ensures the preservation of uploads event after their corresponding database
    | records have been removed.
    |
    */

    'preserve-files' => false,

    'ffmpeg' => [
        /*
        |--------------------------------------------------------------------------
        | FFMPEG Binaries
        |--------------------------------------------------------------------------
        |
        | Larupload can detect your ffmpeg binary path from system environment. but you can set it manually
        | pass from validation.
        |
        | Example: [
        |    'ffmpeg.binaries'  => '/usr/local/bin/ffmpeg',
        |    'ffprobe.binaries' => '/usr/local/bin/ffprobe',
        | ]
        |
        */

        'ffmpeg-binaries'  => 'ffmpeg',
        'ffprobe-binaries' => 'ffprobe',

        /*
        |--------------------------------------------------------------------------
        | FFMPEG Threads
        |--------------------------------------------------------------------------
        |
        | Specify the number of threads that FFMpeg should use
        |
        */

        'threads' => 12,

        /*
        |--------------------------------------------------------------------------
        | FFMPEG Queue
        |--------------------------------------------------------------------------
        |
        | Sometimes ffmpeg process is very heavy, so you have to queue process and do it in background
        | For now, queue is available only for manipulate and stream videos.
        |
        */

        'queue' => false,

        /*
        |--------------------------------------------------------------------------
        | FFMPEG Max Queue Number
        |--------------------------------------------------------------------------
        |
        | Set maximum Larupload instances that currently are queued.
        | Package Will redirect back an error response if maximum limitation exceeded.
        | If you want to ignore this feature and queue uploaded files unlimited, just set 0 for ffmpeg-max-queue-num
        |
        */

        'max-queue-num' => 0,

        /*
        |--------------------------------------------------------------------------
        | FFMPEG Capture Frame
        |--------------------------------------------------------------------------
        |
        | Set Capture frame in second
        |
        | example: null, 0.1, 2
        | When the value is null, larupload will capture a frame from center of video file.
        |
        */

        'capture-frame' => null,

        /*
        |--------------------------------------------------------------------------
        | FFMPEG Timeout
        |--------------------------------------------------------------------------
        |
        | Set timeout to control ffmpeg max execution time
        | To disable the timeout, set this value to null
        |
        */

        'timeout' => 90,

        /*
        |--------------------------------------------------------------------------
        | FFMPEG Log Channel
        |--------------------------------------------------------------------------
        |
        | Set log channel for ffmpeg process
        |
        */

        'log-channel' => env('LOG_CHANNEL', 'stack'),
    ],

    'optimize-image' => [
        /*
        |--------------------------------------------------------------------------
        | Image Optimizer
        |--------------------------------------------------------------------------
        |
        | You can optimize your uploaded images with this option.
        |
        | This package uses spatie/image-optimizer to optimize images.
        | See: https://github.com/spatie/image-optimizer
        |
        */

        'enable' => false,

        /*
        |--------------------------------------------------------------------------
        | Image Optimizers
        |--------------------------------------------------------------------------
        |
        | When calling `optimize` the package will automatically determine which optimizers
        | should run for the given image.
        |
        */

        'optimizers' => [
            Spatie\ImageOptimizer\Optimizers\Jpegoptim::class => [
                '-m85', // set maximum quality to 85%
                '--force', // ensure that progressive generation is always done also if a little bigger
                '--strip-all', // this strips out all text information such as comments and EXIF data
                '--all-progressive', // this will make sure the resulting image is a progressive one
            ],

            Spatie\ImageOptimizer\Optimizers\Pngquant::class => [
                '--force', // required parameter for this package
            ],

            Spatie\ImageOptimizer\Optimizers\Optipng::class => [
                '-i0', // this will result in a non-interlaced, progressive scanned image
                '-o2', // this set the optimization level to two (multiple IDAT compression trials)
                '-quiet', // required parameter for this package
            ],

            Spatie\ImageOptimizer\Optimizers\Svgo::class => [
                '--config=svgo.config.js', // disabling because it is known to cause troubles
            ],

            Spatie\ImageOptimizer\Optimizers\Gifsicle::class => [
                '-b', // required parameter for this package
                '-O3', // this produces the slowest but best results
            ],

            Spatie\ImageOptimizer\Optimizers\Cwebp::class => [
                '-m 6', // for the slowest compression method in order to get the best compression.
                '-pass 10', // for maximizing the amount of analysis pass.
                '-mt', // multithreading for some speed improvements.
                '-q 90', //quality factor that brings the least noticeable changes.
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Image Optimizer Timeout
        |--------------------------------------------------------------------------
        |
        | The maximum time in seconds each optimizer is allowed to run separately.
        |
        */

        'timeout' => 60,
    ]
];
