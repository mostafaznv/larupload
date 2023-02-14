<?php

namespace Mostafaznv\Larupload\Test\Models;

use Illuminate\Database\Eloquent\Model;
use Mostafaznv\Larupload\DTOs\Stream;
use Mostafaznv\Larupload\DTOs\Style;
use Mostafaznv\Larupload\LaruploadEnum;
use Mostafaznv\Larupload\Storage\Attachment;
use Mostafaznv\Larupload\Traits\Larupload;

class LaruploadUploadLight extends Model
{
    use Larupload;

    protected $table = 'upload_light';

    public function attachments(): array
    {
        return [
            Attachment::make('main_file', LaruploadEnum::LIGHT_MODE)
                ->disk('local')
                ->namingMethod(LaruploadEnum::HASH_FILE_NAMING_METHOD)
                ->withMeta(true)
                ->style(
                    Style::make(
                        name: 'small_size',
                        width: 200,
                        height: 200,
                        mode: LaruploadEnum::CROP_STYLE_MODE,
                        type: [
                            LaruploadEnum::IMAGE_STYLE_TYPE, LaruploadEnum::VIDEO_STYLE_TYPE
                        ]
                    )
                )
                ->style(
                    Style::make(
                        name: 'small',
                        width: 200,
                        height: 200,
                        mode: LaruploadEnum::CROP_STYLE_MODE,
                        type: [
                            LaruploadEnum::IMAGE_STYLE_TYPE, LaruploadEnum::VIDEO_STYLE_TYPE
                        ]
                    )
                )
                ->style(
                    Style::make(
                        name: 'medium',
                        width: 800,
                        height: 800,
                        mode: LaruploadEnum::AUTO_STYLE_MODE
                    )
                )
                ->style(
                    Style::make(
                        name: 'landscape',
                        width: 400,
                        mode: LaruploadEnum::LANDSCAPE_STYLE_MODE
                    )
                )
                ->style(
                    Style::make(
                        name: 'portrait',
                        height: 400,
                        mode: LaruploadEnum::PORTRAIT_STYLE_MODE
                    )
                )
                ->style(
                    Style::make(
                        name: 'exact',
                        width: 300,
                        height: 190,
                        mode: LaruploadEnum::EXACT_STYLE_MODE
                    )
                )
                ->style(
                    Style::make(
                        name: 'auto',
                        width: 300,
                        height: 190,
                        mode: LaruploadEnum::AUTO_STYLE_MODE
                    )
                )
                ->stream(
                    Stream::make(
                        name: '480p',
                        width: 640,
                        height: 480,
                        audioBitrate: '64K',
                        videoBitrate: 300000
                    )
                )
                ->stream(
                    Stream::make(
                        name: '720p',
                        width: 1280,
                        height: 720,
                        audioBitrate: '64K',
                        videoBitrate: '1M'
                    )
                )
        ];
    }
}
