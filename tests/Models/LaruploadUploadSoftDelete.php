<?php

namespace Mostafaznv\Larupload\Test\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mostafaznv\Larupload\Enums\LaruploadMediaStyle;
use Mostafaznv\Larupload\Enums\LaruploadMode;
use Mostafaznv\Larupload\Storage\Attachment;
use Mostafaznv\Larupload\Traits\Larupload;

class LaruploadUploadSoftDelete extends Model
{
    use Larupload, SoftDeletes;

    protected $table = 'upload_soft_delete';

    public function attachments(): array
    {
        return [
            Attachment::make('main_file', LaruploadMode::HEAVY)
                ->image('small', 200, 200, LaruploadMediaStyle::CROP)
                ->video('small', 200, 200, LaruploadMediaStyle::CROP)
        ];
    }
}
