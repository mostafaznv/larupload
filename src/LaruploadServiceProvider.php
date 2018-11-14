<?php

namespace Mostafaznv\Larupload;

use Illuminate\Database\Schema\Blueprint as BlueprintIlluminate;
use Illuminate\Support\ServiceProvider;
use Mostafaznv\Larupload\Database\Schema\Blueprint;

class LaruploadServiceProvider extends ServiceProvider
{
    // TODO - dpi for resized/cropped images and videos
    // TODO - test s3
    // TODO - upload with url
    // TODO - write some tests
    // TODO - return file type as string  ['image', 'video', 'audio', 'file']
    // TODO - upload stream videos
    // TODO - import php-ffmpeg package into the project [NOTICE: wait for a stable version]

    const VERSION = '0.0.1';

    public function boot()
    {
        if ($this->app->runningInConsole())
            $this->publishes([__DIR__ . '/../config/config.php' => config_path('larupload.php')], 'config');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'larupload');

        $this->registerMacros();
    }

    protected function registerMacros()
    {
        BlueprintIlluminate::macro('upload', function($name, $mode = 'heavy') {
            Blueprint::columns($this, $name, $mode);
        });

        BlueprintIlluminate::macro('dropUpload', function($name, $mode = 'heavy') {
            Blueprint::dropColumns($this, $name, $mode);
        });
    }
}
