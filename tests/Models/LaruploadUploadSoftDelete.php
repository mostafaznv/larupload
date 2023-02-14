<?php

namespace Mostafaznv\Larupload\Test\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mostafaznv\Larupload\DTOs\Style;
use Mostafaznv\Larupload\LaruploadEnum;
use Mostafaznv\Larupload\Storage\Attachment;
use Mostafaznv\Larupload\Traits\Larupload;

class LaruploadUploadSoftDelete extends Model
{
    use Larupload, SoftDeletes;

    protected $table = 'upload_soft_delete';

    public function attachments(): array
    {
        return [
            Attachment::make('main_file', LaruploadEnum::HEAVY_MODE)
                ->style(
                    Style::make(
                        name: 'small',
                        width: 200,
                        height: 200,
                        mode: LaruploadEnum::CROP_STYLE_MODE
                    )
                )
        ];
    }
}
