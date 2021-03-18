<?php

namespace Mostafaznv\Larupload;

use Illuminate\Database\Schema\Blueprint as BlueprintIlluminate;
use Illuminate\Support\ServiceProvider;
use Mostafaznv\Larupload\Database\Schema\Blueprint;

class LaruploadServiceProvider extends ServiceProvider
{
    // TODO - automatically attach file into model after toArray/toJson
    // TODO - update m3u8 catalog
    // TODO - Return Meta Object on Light Mode when attached file is null
    // TODO - upload with url
    // TODO - add an ability to upload files without orm.
    // TODO - dpi for resized/cropped images and videos
    // TODO - test s3
    // TODO - import php-ffmpeg package into the project [NOTICE: wait for a stable version]
    // TODO - add an ability to custom ffmpeg scripts (video and stream)
    // TODO - check possibility of change video ffmpeg script to streaming one (none crop mode).
    // TODO - mix with download php-x-sendfile
    // TODO - return original size of file on json response (toString and toJson)
    // TODO - upload with create() function

    const VERSION = '0.0.9';

    public function boot()
    {
        $this->loadTranslationsFrom(__DIR__ . '/../translations', 'larupload');

        if ($this->app->runningInConsole()) {
            $this->publishes([__DIR__ . '/../config/config.php' => config_path('larupload.php')], 'config');
            $this->publishes([__DIR__ . '/../migrations/' => database_path('migrations')], 'migrations');
            $this->publishes([__DIR__ . '/../translations/' => resource_path('lang/vendor/larupload')]);
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'larupload');

        $this->registerMacros();
    }

    protected function registerMacros()
    {
        BlueprintIlluminate::macro('upload', function(string $name, string $mode = 'heavy') {
            Blueprint::columns($this, $name, $mode);
        });

        BlueprintIlluminate::macro('dropUpload', function(string $name, string $mode = 'heavy') {
            Blueprint::dropColumns($this, $name, $mode);
        });
    }
}
