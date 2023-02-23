<?php

namespace Mostafaznv\Larupload\Test\Models;

use Illuminate\Database\Eloquent\Model;
use Mostafaznv\Larupload\DTOs\Stream;
use Mostafaznv\Larupload\DTOs\Style;
use Mostafaznv\Larupload\Enums\LaruploadMode;
use Mostafaznv\Larupload\Enums\LaruploadNamingMethod;
use Mostafaznv\Larupload\Enums\LaruploadStyleMode;
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
            Attachment::make('main_file', LaruploadMode::HEAVY)
                ->disk('local')
                ->namingMethod(LaruploadNamingMethod::HASH_FILE)
                ->withMeta(true)
                ->style(
                    Style::make(
                        name: 'small_size',
                        width: 200,
                        height: 200,
                        mode: LaruploadStyleMode::CROP,
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
                        mode: LaruploadStyleMode::CROP,
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
                        mode: LaruploadStyleMode::AUTO
                    )
                )
                ->style(
                    Style::make(
                        name: 'landscape',
                        width: 400,
                        mode: LaruploadStyleMode::LANDSCAPE
                    )
                )
                ->style(
                    Style::make(
                        name: 'portrait',
                        height: 400,
                        mode: LaruploadStyleMode::PORTRAIT
                    )
                )
                ->style(
                    Style::make(
                        name: 'exact',
                        width: 300,
                        height: 190,
                        mode: LaruploadStyleMode::EXACT
                    )
                )
                ->style(
                    Style::make(
                        name: 'auto',
                        width: 300,
                        height: 190,
                        mode: LaruploadStyleMode::AUTO
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
