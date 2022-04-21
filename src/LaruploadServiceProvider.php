<?php

namespace Mostafaznv\Larupload;

use Illuminate\Database\Schema\Blueprint as BlueprintIlluminate;
use Illuminate\Support\ServiceProvider;
use Mostafaznv\Larupload\Database\Schema\Blueprint;

class LaruploadServiceProvider extends ServiceProvider
{
    // TODO - use hashids/hashids instead of actual model id in file path (path/model/id/file ==> path/model/hashid/file)
    // TODO - upload with create() function

    // TODO - update m3u8 catalog
    // TODO - import php-ffmpeg package into the project [NOTICE: wait for a stable version]
    // TODO - add an ability to custom ffmpeg scripts (video and stream)
    // TODO - check possibility of change video ffmpeg script to streaming one (none crop mode).

    // TODO - dpi for resized/cropped images and videos
    // TODO - test s3
    // TODO - mix with download php-x-sendfile
    // TODO - add some comments to help IDEs to show attachment functions

    const VERSION = '0.1.1';

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
        BlueprintIlluminate::macro('upload', function(string $name, string $mode = LaruploadEnum::HEAVY_MODE) {
            Blueprint::columns($this, $name, $mode);
        });

        BlueprintIlluminate::macro('dropUpload', function(string $name, string $mode = LaruploadEnum::HEAVY_MODE) {
            Blueprint::dropColumns($this, $name);
        });
    }
}
