# Image Optimization

Larupload provides a feature to optimize images, which uses the [`spatie/image-optimizer`](https://github.com/spatie/image-optimizer) package. This feature is disabled by default, but can be enabled by configuring the `optimize` option in the Larupload configuration file. Once enabled, the package will automatically optimize the images upon upload, reducing their file size without compromising their quality. You can also set a custom optimizer for each image type (jpg, png, svg, gif, webp) by providing a configuration for each. This feature can significantly improve the performance of your application by reducing the size of images being served.



{% code title="config/larupload.php" %}
```php
<?php

return [
    'optimize-image' => [
        'enable'  => false,
        'timeout' => 60,

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
        ]
    ]
];
```
{% endcode %}



