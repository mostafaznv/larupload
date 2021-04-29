<?php

namespace Mostafaznv\Larupload\Test\Models;

use Illuminate\Database\Eloquent\Model;
use Mostafaznv\Larupload\LaruploadEnum;
use Mostafaznv\Larupload\Storage\Attachment;
use Mostafaznv\Larupload\Traits\Larupload;

class LaruploadUploadHeavy extends Model
{
    use Larupload;

    protected $table = 'upload_heavy';

    public function attachments(): array
    {
        return [
            Attachment::make('main_file', LaruploadEnum::HEAVY_MODE)
                ->disk('local')
                ->namingMethod(LaruploadEnum::HASH_FILE_NAMING_METHOD)
                ->withMeta(true)
                ->style('small_size', 200, 200, LaruploadEnum::CROP_STYLE_MODE, [LaruploadEnum::IMAGE_STYLE_TYPE, LaruploadEnum::VIDEO_STYLE_TYPE])
                ->style('small', 200, 200, LaruploadEnum::CROP_STYLE_MODE, [LaruploadEnum::IMAGE_STYLE_TYPE, LaruploadEnum::VIDEO_STYLE_TYPE])
                ->style('medium', 800, 800, LaruploadEnum::AUTO_STYLE_MODE)
                ->style('landscape', 400, null, LaruploadEnum::LANDSCAPE_STYLE_MODE)
                ->style('portrait', null, 400, LaruploadEnum::PORTRAIT_STYLE_MODE)
                ->style('exact', 300, 190, LaruploadEnum::EXACT_STYLE_MODE)
                ->style('auto', 300, 190, LaruploadEnum::AUTO_STYLE_MODE)
                ->stream('480p', 640, 480, '64k', '300000')
                ->stream('720p', 1280, 720, '64K', '1M')
        ];
    }
}
