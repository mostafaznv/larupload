<?php

namespace Mostafaznv\Larupload\Test\Support\Models;

use Illuminate\Database\Eloquent\Model;
use Mostafaznv\Larupload\Enums\LaruploadMediaStyle;
use Mostafaznv\Larupload\Enums\LaruploadMode;
use Mostafaznv\Larupload\Enums\LaruploadNamingMethod;
use Mostafaznv\Larupload\Storage\Attachment;
use Mostafaznv\Larupload\Traits\Larupload;

class LaruploadLightTestModel extends Model
{
    use Larupload;

    protected $table = 'upload_light';

    public function attachments(): array
    {
        return [
            Attachment::make('main_file', LaruploadMode::LIGHT)
                ->disk('local')
                ->namingMethod(LaruploadNamingMethod::HASH_FILE)
                ->withMeta(true)
                ->image('small_size', 200, 200, LaruploadMediaStyle::CROP)
                ->image('small', 200, 200, LaruploadMediaStyle::CROP)
                ->image('medium', 800, 800, LaruploadMediaStyle::AUTO)
                ->image('landscape', 400, null, LaruploadMediaStyle::SCALE_HEIGHT)
                ->image('portrait', null, 400, LaruploadMediaStyle::SCALE_WIDTH)
                ->image('exact', 300, 190, LaruploadMediaStyle::FIT)
                ->image('auto', 300, 190, LaruploadMediaStyle::AUTO)
                ->video('small_size', 200, 200, LaruploadMediaStyle::CROP)
                ->video('small', 200, 200, LaruploadMediaStyle::CROP)
                ->video('medium', 800, 800, LaruploadMediaStyle::AUTO)
                ->video('landscape', 400, null, LaruploadMediaStyle::SCALE_HEIGHT)
                ->video('portrait', null, 400, LaruploadMediaStyle::SCALE_WIDTH)
                ->video('exact', 300, 190, LaruploadMediaStyle::FIT)
                ->video('auto', 300, 190, LaruploadMediaStyle::AUTO)
                ->stream('480p', 640, 480, 64, 3000)
                ->stream('720p', 1280, 720, 64, 1000)
        ];
    }
}
