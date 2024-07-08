<?php

namespace Mostafaznv\Larupload;

use Illuminate\Database\Schema\Blueprint as BlueprintIlluminate;
use Illuminate\Support\ServiceProvider;
use Mostafaznv\Larupload\Database\Schema\Blueprint;
use Mostafaznv\Larupload\Enums\LaruploadMode;

class LaruploadServiceProvider extends ServiceProvider
{
    // TODO - throw a custom exception when ffmpeg queue exceeds the limit
    // TODO - remove meta-data from file

    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/../translations', 'larupload');

        if ($this->app->runningInConsole()) {
            $this->publishes([__DIR__ . '/../config/config.php' => config_path('larupload.php')], 'config');
            $this->publishes([__DIR__ . '/../migrations/' => database_path('migrations')], 'migrations');
            $this->publishes([__DIR__ . '/../translations/' => lang_path('vendor/larupload')]);
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'larupload');

        $this->registerMacros();
    }

    protected function registerMacros(): void
    {
        BlueprintIlluminate::macro('upload', function(string $name, LaruploadMode $mode = LaruploadMode::HEAVY) {
            Blueprint::columns($this, $name, $mode);
        });

        BlueprintIlluminate::macro('dropUpload', function(string $name, LaruploadMode $mode = LaruploadMode::HEAVY) {
            Blueprint::dropColumns($this, $name, $mode);
        });

        BlueprintIlluminate::macro('laruploadAddOriginalName', function(string $name) {
            Blueprint::addOriginalName($this, $name);
        });
    }
}
