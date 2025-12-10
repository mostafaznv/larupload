<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Storage Disk
    |--------------------------------------------------------------------------
    |
    | The default filesystem disk used to store uploaded files
    | Larupload will use any disk defined in `config/filesystems.php`.
    |
    */

    'disk' => 'local',

    /*
    |--------------------------------------------------------------------------
    | Local Storage Disk
    |--------------------------------------------------------------------------
    |
    | The name of your local filesystem disk. When your default disk uses
    | an external driver (for example, S3 or SFTP), Larupload may require a local
    | disk for certain operations.
    |
    | Note: In most setups the local disk is the same as the default disk.
    |
    */

    'local-disk' => 'local',

    /*
    |--------------------------------------------------------------------------
    | Mode
    |--------------------------------------------------------------------------
    |
    | Larupload supports two operating modes: light and heavy.
    | - light: stores the file name in the database and metadata in a JSON column named `meta`.
    | - heavy: stores additional, discrete columns for more detailed metadata.
    |
    */

    'mode' => Mostafaznv\Larupload\Enums\LaruploadMode::HEAVY,

    /*
    |--------------------------------------------------------------------------
    | Secure IDs
    |--------------------------------------------------------------------------
    |
    | Optionally mask model record IDs in file paths for privacy or security.
    | Supported methods:
    | - ULID
    | - UUID
    | - SQID (requires the `sqids/sqids` package)
    | - HASHID (requires the `hashids/hashids` package)
    | - NONE (use actual numeric IDs)
    |
    */

    'secure-ids' => Mostafaznv\Larupload\Enums\LaruploadSecureIdsMethod::NONE,

    /*
    |--------------------------------------------------------------------------
    | Include Metadata in Responses
    |--------------------------------------------------------------------------
    |
    | When enabled, returned URLs will include associated metadata.
    | Set to `true` to include metadata, `false` to exclude it.
    |
    */

    'with-meta' => true,

    /*
    |--------------------------------------------------------------------------
    | Camel Case Response
    |--------------------------------------------------------------------------
    |
    | When enabled, metadata keys are returned in camelCase. When disabled,
    | metadata keys are returned in snake_case.
    |
    */

    'camel-case-response' => false,

    /*
    |--------------------------------------------------------------------------
    | Hide Internal Table Columns
    |--------------------------------------------------------------------------
    |
    | Larupload creates several internal columns to manage uploads. These
    | columns are typically not relevant at the application or API layer.
    | Enable this option to hide them from `toArray` and `toJson`.
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
    /*
    |--------------------------------------------------------------------------
    | Naming Method
    |--------------------------------------------------------------------------
    |
    | Strategy used to generate stored file names.
    |
    | Note: when using `slug`, Larupload appends an incrementing suffix to
    | prevent collisions and client-side caching issues for files with the
    | same original name.
    |
    */

    'naming-method' => Mostafaznv\Larupload\Enums\LaruploadNamingMethod::HASH_FILE,
    'lang'          => '',

    /*
    |--------------------------------------------------------------------------
    | Image Processing Library
    |--------------------------------------------------------------------------
    |
    | Library used for image manipulation.
    |
    */

    'image-processing-library' => Mostafaznv\Larupload\Enums\LaruploadImageLibrary::GD,

    /*
    |--------------------------------------------------------------------------
    | Generate Cover Images
    |--------------------------------------------------------------------------
    |
    | When enabled, Larupload will generate a cover image (thumbnail) for
    | supported image and video files.
    |
    */

    'generate-cover' => true,

    /*
    |--------------------------------------------------------------------------
    | Cover Style
    |--------------------------------------------------------------------------
    |
    | Configuration for generated cover images. Covers are only produced
    | when the `generate-cover` flag is true.
    | The resulting cover data is stored in dedicated cover columns.
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
    | Dominant Color Extraction
    |--------------------------------------------------------------------------
    |
    | When enabled, Larupload will attempt to extract the dominant color
    | from images and videos.
    |
    */

    'dominant-color' => true,

    /*
    |--------------------------------------------------------------------------
    | Dominant Color Quality
    |--------------------------------------------------------------------------
    |
    | Controls the accuracy and performance of dominant color calculation.
    | Lower values yield higher quality but require more memory and compute.
    | Typical range: 1 (the highest quality) to 10 (default).
    |
    */

    'dominant-color-quality' => 10,

    /*
    |--------------------------------------------------------------------------
    | Keep Old Files
    |--------------------------------------------------------------------------
    |
    | When true, previous uploads will not be deleted from storage when a
    | record is updated with a new file. When false, old files are removed.
    |
    */

    'keep-old-files' => false,

    /*
    |--------------------------------------------------------------------------
    | Preserve Files After Deletion
    |--------------------------------------------------------------------------
    |
    | When true, uploaded files are preserved on disk even after their
    | corresponding database records are deleted. When false, files are removed.
    |
    */

    'preserve-files' => false,

    'ffmpeg' => [
        /*
        |--------------------------------------------------------------------------
        | FFMPEG Binaries
        |--------------------------------------------------------------------------
        |
        | Paths to the ffmpeg and ffprobe binaries. Larupload can detect these
        | automatically from the environment, but you may set them explicitly.
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
        | Number of threads FFMpeg should use for processing.
        |
        */

        'threads' => 12,

        /*
        |--------------------------------------------------------------------------
        | FFMPEG Queue Processing
        |--------------------------------------------------------------------------
        |
        | Enable queuing for heavy FFMpeg operations to run them asynchronously.
        | Currently, queueing applies to video manipulation and streaming tasks.
        |
        */

        'queue' => false,

        /*
        |--------------------------------------------------------------------------
        | Maximum FFMPEG Queue Size
        |--------------------------------------------------------------------------
        |
        | Maximum number of Larupload instances allowed in the queue concurrently.
        | Set to `0` to disable the limit (unlimited queue).
        |
        */

        'max-queue-num' => 0,

        /*
        |--------------------------------------------------------------------------
        | FFMPEG Capture Frame
        |--------------------------------------------------------------------------
        |
        | Time (in seconds) at which to capture a video frame for thumbnails.
        | Use `null` to capture a frame from the middle of the video.
        | Examples: null, 0.1, 2
        |
        */

        'capture-frame' => null,

        /*
        |--------------------------------------------------------------------------
        | FFMPEG Timeout
        |--------------------------------------------------------------------------
        |
        | Maximum execution time in seconds for FFMpeg processes. Set to `null`
        | to disable the timeout.
        |
        */

        'timeout' => 90,

        /*
        |--------------------------------------------------------------------------
        | FFMPEG Log Channel
        |--------------------------------------------------------------------------
        |
        | The application log channel to use for ffmpeg-related logging.
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
        | Image Optimizer Configurations
        |--------------------------------------------------------------------------
        |
        | Per-optimizer options. The package will choose appropriate optimizers
        | based on the image format when optimization is requested.
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
        | Maximum time in seconds allowed for each optimizer to run.
        |
        */

        'timeout' => 60,
    ]
];
