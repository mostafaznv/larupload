# Media Styles

## Media Styles

This note explains the usage of the `stream`, `image`, and `video` functions in Larupload.

The `stream` function is used to create an HTTP Live Streaming (HLS) stream, which is a video streaming protocol that breaks the video into smaller segments and delivers them over HTTP. The `image` and `video` functions are used to manipulate images and videos, respectively.

When you want to create an HLS stream for a video, you can use the `stream` function available in the `Attachment` class of the `attachments` method within their model. This function takes some arguments that specify the stream's resolution, bitrate, and other properties.

On the other hand, if you want to manipulate images or videos, you should use the `image` and `video` functions, respectively. These functions provide a set of options for resizing, cropping, and modifying the image or video.&#x20;



### Image Style

<table><thead><tr><th width="100" data-type="number">Index</th><th width="99">Name</th><th width="196">Type</th><th data-type="checkbox">Required</th><th width="91">Default</th><th width="515">Description</th></tr></thead><tbody><tr><td>1</td><td>name</td><td>string</td><td>true</td><td>–</td><td>style name. examples: thumbnail, small, ...</td></tr><tr><td>2</td><td>width</td><td>?int</td><td>false</td><td>null</td><td>width of the manipulated image</td></tr><tr><td>3</td><td>height</td><td>?int</td><td>false</td><td>null</td><td>height of the manipulated image</td></tr><tr><td>4</td><td>mode</td><td>LaruploadMediaStyle</td><td>false</td><td>AUTO</td><td>this argument specifies how Larupload should manipulate the uploaded image and can take on any of the following values: <code>FIT</code>, <code>AUTO</code>, <code>SCALE_WIDTH</code>, <code>SCALE_HEIGHT</code>, <code>CROP</code></td></tr></tbody></table>

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Mostafaznv\Larupload\Enums\LaruploadMediaStyle;
use Mostafaznv\Larupload\Storage\Attachment;
use Mostafaznv\Larupload\Traits\Larupload;

class Media extends Model
{
    use Larupload;

    public function attachments(): array
    {
        return [
            Attachment::make('file')
                ->image('thumbnail', 250, 250, LaruploadMediaStyle::CROP)
                ->image('landscape', 1100, 1100, LaruploadMediaStyle::AUTO)
        ];
    }
}
```







### Audio Style

<table><thead><tr><th width="103" data-type="number">Index</th><th width="99">Name</th><th width="196">Type</th><th data-type="checkbox">Required</th><th width="155">Default</th><th width="515">Description</th></tr></thead><tbody><tr><td>1</td><td>name</td><td>string</td><td>true</td><td>–</td><td>style name. examples: hq, lq, ...</td></tr><tr><td>2</td><td>format</td><td>Mp3|Aac|Wav|Flac</td><td>false</td><td>Mp3</td><td>the format of the converted audio file</td></tr></tbody></table>

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Mostafaznv\Larupload\Enums\LaruploadMediaStyle;
use Mostafaznv\Larupload\Storage\Attachment;
use Mostafaznv\Larupload\Traits\Larupload;
use FFMpeg\Format\Audio\Wav;

class Media extends Model
{
    use Larupload;

    public function attachments(): array
    {
        return [
            Attachment::make('file')
                ->audio('hq', new Wav())
        ];
    }
}
```







### Video Style

<table><thead><tr><th width="103" data-type="number">Index</th><th width="99">Name</th><th width="196">Type</th><th data-type="checkbox">Required</th><th width="155">Default</th><th width="515">Description</th></tr></thead><tbody><tr><td>1</td><td>name</td><td>string</td><td>true</td><td>–</td><td>style name. examples: thumbnail, small, ...</td></tr><tr><td>2</td><td>width</td><td>?int</td><td>false</td><td>null</td><td>width of the manipulated video</td></tr><tr><td>3</td><td>height</td><td>?int</td><td>false</td><td>null</td><td>height of the manipulated video</td></tr><tr><td>4</td><td>mode</td><td>LaruploadMediaStyle</td><td>false</td><td>SCALE_HEIGHT</td><td>this argument specifies how Larupload should manipulate the uploaded video and can take on any of the following values: <code>FIT</code>, <code>AUTO</code>, <code>SCALE_WIDTH</code>, <code>SCALE_HEIGHT</code>, <code>CROP</code></td></tr><tr><td>5</td><td>format</td><td>X264</td><td>false</td><td>new X264</td><td>by default, the encoding format for video is <code>X264</code>. However, users can specify additional options for this format, including adjusting the <code>kilobitrate</code> for both <code>audio</code> and <code>video</code>. This allows for more precise configuration and optimization of the user's encoding preferences.</td></tr><tr><td>6</td><td>padding</td><td>bool</td><td>false</td><td>false</td><td>If set to <code>true</code>, padding will be applied to the video using a black color in order to fit the given dimensions.</td></tr></tbody></table>

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Mostafaznv\Larupload\Enums\LaruploadMediaStyle;
use Mostafaznv\Larupload\Storage\Attachment;
use Mostafaznv\Larupload\Traits\Larupload;

class Media extends Model
{
    use Larupload;

    public function attachments(): array
    {
        return [
            Attachment::make('file')
                ->video('thumbnail', 250, 250, LaruploadMediaStyle::CROP)
                ->video('landscape', 1100, 1100, LaruploadMediaStyle::AUTO)
        ];
    }
}
```





### Stream Style

<table><thead><tr><th width="93" data-type="number">Index</th><th width="99">Name</th><th width="87">Type</th><th data-type="checkbox">Required</th><th width="91">Default</th><th width="515">Description</th></tr></thead><tbody><tr><td>1</td><td>name</td><td>string</td><td>true</td><td>–</td><td>label for stream quality. highly recommended to use string labels like <code>720p</code></td></tr><tr><td>2</td><td>width</td><td>int</td><td>true</td><td>–</td><td></td></tr><tr><td>3</td><td>height</td><td>int</td><td>true</td><td>–</td><td></td></tr><tr><td>4</td><td>format</td><td>X264</td><td>true</td><td>–</td><td>by default, the encoding format for video is <code>X264</code>. However, users can specify additional options for this format, including adjusting the <code>kilobitrate</code> for both <code>audio</code> and <code>video</code>. This allows for more precise configuration and optimization of the user's encoding preferences.</td></tr><tr><td>5</td><td>padding</td><td>bool</td><td>false</td><td>false</td><td>If set to <code>true</code>, padding will be applied to the video using a black color in order to fit the given dimensions.</td></tr></tbody></table>

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use FFMpeg\Format\Video\X264;
use Mostafaznv\Larupload\Storage\Attachment;
use Mostafaznv\Larupload\Traits\Larupload;

class Media extends Model
{
    use Larupload;

    public function attachments(): array
    {
        return [
            Attachment::make('file')
                ->stream(
                    name: '480p',
                    width: 640,
                    height: 480,
                    format: (new X264)
                        ->setKiloBitrate(1000)
                        ->setAudioKiloBitrate(32)
                )
                ->stream(
                    name: '720p',
                    width: 1280,
                    height:  720,
                    format: (new X264)
                        ->setKiloBitrate(3000)
                        ->setAudioKiloBitrate(64)
                )
        ];
    }
}
```



