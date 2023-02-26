<?php

namespace Mostafaznv\Larupload\Test\Models;

use Illuminate\Database\Eloquent\Model;
use Mostafaznv\Larupload\Enums\LaruploadMode;
use Mostafaznv\Larupload\Enums\LaruploadNamingMethod;
use Mostafaznv\Larupload\Enums\Style\LaruploadImageStyleMode;
use Mostafaznv\Larupload\Enums\Style\LaruploadVideoStyleMode;
use Mostafaznv\Larupload\Storage\Attachment;
use Mostafaznv\Larupload\Traits\Larupload;

class LaruploadUploadHeavy extends Model
{
    use Larupload;

    protected $table = 'upload_heavy';

    public function attachments(): array
    {
        return [
            Attachment::make('main_file', LaruploadMode::HEAVY)
                ->disk('local')
                ->namingMethod(LaruploadNamingMethod::HASH_FILE)
                ->withMeta(true)
                ->image('small_size', 200, 200, LaruploadImageStyleMode::CROP)
                ->image('small', 200, 200, LaruploadImageStyleMode::CROP)
                ->image('medium', 800, 800, LaruploadImageStyleMode::AUTO)
                ->image('landscape', 400, null, LaruploadImageStyleMode::LANDSCAPE)
                ->image('portrait', null, 400, LaruploadImageStyleMode::PORTRAIT)
                ->image('exact', 300, 190, LaruploadImageStyleMode::EXACT)
                ->image('auto', 300, 190, LaruploadImageStyleMode::AUTO)
                ->video('small_size', 200, 200, LaruploadVideoStyleMode::CROP)
                ->video('small', 200, 200, LaruploadVideoStyleMode::CROP)
                ->video('medium', 800, 800, LaruploadVideoStyleMode::INSET)
                ->video('landscape', 400, null, LaruploadVideoStyleMode::SCALE_HEIGHT)
                ->video('portrait', null, 400, LaruploadVideoStyleMode::SCALE_WIDTH)
                ->video('exact', 300, 190, LaruploadVideoStyleMode::FIT)
                ->video('auto', 300, 190, LaruploadVideoStyleMode::INSET)
                ->stream('480p', 640, 480, '64K', 300000)
                ->stream('720p', 1280, 720, '64K', '1M')
        ];
    }
}
