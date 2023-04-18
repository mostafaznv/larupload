# Complete Example

```php
<?php

namespace App\Models;

use FFMpeg\Format\Video\X264;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mostafaznv\Larupload\Enums\LaruploadImageLibrary;
use Mostafaznv\Larupload\Enums\LaruploadMediaStyle;
use Mostafaznv\Larupload\Enums\LaruploadMode;
use Mostafaznv\Larupload\Enums\LaruploadNamingMethod;
use Mostafaznv\Larupload\Enums\LaruploadStyleMode;
use Mostafaznv\Larupload\Enums\LaruploadSecureIdsMethod;
use Mostafaznv\Larupload\Storage\Attachment;
use Mostafaznv\Larupload\Traits\Larupload;

class Media extends Model
{
    use Larupload;

    public function attachments(): array
    {
        return [
            Attachment::make('main_file')
                ->disk('local')
                ->withMeta(true)
                ->namingMethod(LaruploadNamingMethod::HASH_FILE)
                ->lang('fa')
                ->imageProcessingLibrary(LaruploadImageLibrary::GD)
                ->generateCover(false)
                ->dominantColor(true)
                ->dominantColorQuality(5)
                ->keepOldFiles(true)
                ->preserveFiles(true)
                ->secureIdsMethod(LaruploadSecureIdsMethod::ULID)
                ->optimizeImage(true)
                ->coverStyle('cover', 400, 400, LaruploadMediaStyle::CROP)
                ->image('thumbnail', 250, 250, LaruploadMediaStyle::AUTO)
                ->image('crop_mode', 1100, 1100, LaruploadMediaStyle::CROP)
                ->image('portrait_mode', 1000, 1000, LaruploadMediaStyle::SCALE_WIDTH)
                ->video('thumbnail', 250, 250, LaruploadMediaStyle::AUTO)
                ->video('crop_mode', 1100, 1100, LaruploadMediaStyle::CROP)
                ->video('portrait_mode', 1000, 1000, LaruploadMediaStyle::SCALE_WIDTH)
                ->video(
                    name: 'auto',
                    width: 300,
                    height: 190,
                    mode: LaruploadMediaStyle::AUTO,
                    format: (new X264)
                        ->setKiloBitrate(1000)
                        ->setAudioKiloBitrate(64)
                )
                ->stream(
                    name: '480p',
                    width: 640,
                    height: 480,
                    format: (new X264)
                        ->setKiloBitrate(3000)
                        ->setAudioKiloBitrate(64)
                )
                ->stream(
                    name: '720p',
                    width: 1280,
                    height:  720,
                    format: (new X264)
                        ->setKiloBitrate(1000)
                        ->setAudioKiloBitrate(64)
                )

            Attachment::make('other_file', LaruploadMode::LIGHT)
                ->stream(
                    name: '480p',
                    width: 640,
                    height: 480,
                    format: (new X264)
                        ->setKiloBitrate(3000)
                        ->setAudioKiloBitrate(64)
                        ->setAudioChannels(1)
                        ->setAudioCodec('libmp3lame')
                ),
        ];
    }
}
```
