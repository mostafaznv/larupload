<?php

use FFMpeg\Format\Video\X264;
use Mostafaznv\Larupload\Enums\LaruploadMediaStyle;
use Mostafaznv\Larupload\Enums\LaruploadNamingMethod;
use Mostafaznv\Larupload\Larupload;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadHeavyTestModel;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadLightTestModel;
use Mostafaznv\Larupload\Enums\LaruploadSecureIdsMethod;
use Illuminate\Support\Str;
use Mostafaznv\Larupload\Test\Support\Enums\LaruploadTestModels;
use Mostafaznv\Larupload\Storage\Attachment;
use Mostafaznv\Larupload\Test\Support\TestAttachmentBuilder;

it('will generate video styles correctly', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $model->withAllVideos();
    $model = save($model, mp4());

    $attachment = $model->attachment('main_file');
    $cover = urlToVideo($attachment->url('cover'));
    $small = urlToVideo($attachment->url('small'));
    $medium = urlToVideo($attachment->url('medium'));
    $landscape = urlToVideo($attachment->url('landscape'));
    $portrait = urlToVideo($attachment->url('portrait'));
    $exact = urlToVideo($attachment->url('exact'));
    $auto = urlToVideo($attachment->url('auto'));

    expect($attachment->url('cover'))
        ->toBeTruthy()
        ->toBeString()
        ->toBeExists()
        ->and($cover->width)
        ->toBe(500)
        ->and($cover->height)
        ->toBe(500)
        ->and($cover->duration)
        ->toBe(0)
        //
        ->and($attachment->url('small'))
        ->toBeTruthy()
        ->toBeString()
        ->toBeExists()
        ->and($small->width)
        ->toBe(200)
        ->and($small->height)
        ->toBe(200)
        ->and($small->duration)
        ->toBe(5)
        //
        ->and($attachment->url('medium'))
        ->toBeTruthy()
        ->toBeString()
        ->toBeExists()
        ->and($medium->width)
        ->toBe(800)
        ->and($medium->height)
        ->toBe(800)
        ->and($medium->duration)
        ->toBe(5)
        //
        ->and($attachment->url('landscape'))
        ->toBeTruthy()
        ->toBeString()
        ->toBeExists()
        ->and($landscape->width)
        ->toBe(400)
        ->and($landscape->height)
        ->toBe(226)
        ->and($landscape->duration)
        ->toBe(5)
        //
        ->and($attachment->url('portrait'))
        ->toBeTruthy()
        ->toBeString()
        ->toBeExists()
        ->and($portrait->width)
        ->toBe(712)
        ->and($portrait->height)
        ->toBe(400)
        ->and($portrait->duration)
        ->toBe(5)
        //
        ->and($attachment->url('exact'))
        ->toBeTruthy()
        ->toBeString()
        ->toBeExists()
        ->and($exact->width)
        ->toBe(300)
        ->and($exact->height)
        ->toBe(190)
        ->and($exact->duration)
        ->toBe(5)
        //
        ->and($attachment->url('auto'))
        ->toBeTruthy()
        ->toBeString()
        ->toBeExists()
        ->and($auto->width)
        ->toBe(300)
        ->and($auto->height)
        ->toBe(168)
        ->and($auto->duration)
        ->toBe(5);

})->with('models');

it('will generate videos with custom video/audio formats', function() {
    $model = new LaruploadHeavyTestModel;
    $model->withAllCustomFormatVideos();
    $model = save($model, mp4());

    $attachment = $model->attachment('main_file');
    $cover = urlToVideo($attachment->url('cover'));
    $webm = urlToVideo($attachment->url('webm'));
    $ogg = urlToVideo($attachment->url('ogg'));
    $mp3 = urlToAudio($attachment->url('mp3'));
    $wav = urlToAudio($attachment->url('wav'));
    $flac = urlToAudio($attachment->url('flac'));

    expect($attachment->url('cover'))
        ->toBeTruthy()
        ->toBeString()
        ->toBeExists()
        ->and($cover->width)
        ->toBe(500)
        ->and($cover->height)
        ->toBe(500)
        ->and($cover->duration)
        ->toBe(0)
        // web
        ->and($attachment->url('webm'))
        ->toBeTruthy()
        ->toBeString()
        ->toBeExists()
        ->and($webm->width)
        ->toBe(400)
        ->and($webm->height)
        ->toBe(224)
        ->and($webm->duration)
        ->toBe(5)
        // ogg
        ->and($attachment->url('ogg'))
        ->toBeTruthy()
        ->toBeString()
        ->toBeExists()
        ->and($ogg->width)
        ->toBe(400)
        ->and($ogg->height)
        ->toBe(224)
        ->and($ogg->duration)
        ->toBe(5)
        // mp3
        ->and($attachment->url('mp3'))
        ->toBeTruthy()
        ->toBeString()
        ->toBeExists()
        ->and($mp3->width)
        ->toBeNull()
        ->and($mp3->height)
        ->toBeNull()
        ->and($mp3->duration)
        ->toBe(5)
        // wav
        ->and($attachment->url('wav'))
        ->toBeTruthy()
        ->toBeString()
        ->toBeExists()
        ->and($wav->width)
        ->toBeNull()
        ->and($wav->height)
        ->toBeNull()
        ->and($wav->duration)
        ->toBe(5)
        // flac
        ->and($attachment->url('flac'))
        ->toBeTruthy()
        ->toBeString()
        ->toBeExists()
        ->and($flac->width)
        ->toBeNull()
        ->and($flac->height)
        ->toBeNull()
        ->and($flac->duration)
        ->toBe(5);

});

it('will generate stream correctly', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $model->withStreams();
    $model = save($model, mp4());

    $baseUrl = url('/');
    $url = str_replace($baseUrl, '', $model->attachment('main_file')->url('stream'));
    $path = public_path($url);
    $dir = pathinfo($path, PATHINFO_DIRNAME);

    $folders = ['480p', '720p'];

    expect(file_exists($path))->toBeTrue();

    foreach ($folders as $folder) {
        expect(file_exists($dir . '/' . $folder . '/' . "$folder-list.m3u8"))
            ->toBeTrue()
            ->and(file_exists($dir . '/' . $folder . '/' . "$folder-0.ts"))
            ->toBeTrue();
    }

})->with('models');

it('will generate video styles in standalone mode correctly', function() {
    $upload = Larupload::init('uploader')
        ->video('small', 200, 200, LaruploadMediaStyle::CROP)
        ->video('medium', 800, 800, LaruploadMediaStyle::AUTO)
        ->video('landscape', 400, null, LaruploadMediaStyle::SCALE_HEIGHT)
        ->video('portrait', null, 400, LaruploadMediaStyle::SCALE_WIDTH)
        ->video('exact', 300, 190, LaruploadMediaStyle::FIT)
        ->video('auto', 300, 190, LaruploadMediaStyle::AUTO)
        ->upload(mp4());

    $cover = urlToVideo($upload->cover);
    $small = urlToVideo($upload->small);
    $medium = urlToVideo($upload->medium);
    $landscape = urlToVideo($upload->landscape);
    $portrait = urlToVideo($upload->portrait);
    $exact = urlToVideo($upload->exact);
    $auto = urlToVideo($upload->auto);

    expect($upload->cover)
        ->toBeTruthy()
        ->toBeString()
        ->toBeExists()
        ->and($cover->width)
        ->toBe(500)
        ->and($cover->height)
        ->toBe(500)
        ->and($cover->duration)
        ->toBe(0)
        //
        ->and($upload->small)
        ->toBeTruthy()
        ->toBeString()
        ->toBeExists()
        ->and($small->width)
        ->toBe(200)
        ->and($small->height)
        ->toBe(200)
        ->and($small->duration)
        ->toBe(5)
        //
        ->and($upload->medium)
        ->toBeTruthy()
        ->toBeString()
        ->toBeExists()
        ->and($medium->width)
        ->toBe(800)
        ->and($medium->height)
        ->toBe(450)
        ->and($medium->duration)
        ->toBe(5)
        //
        ->and($upload->landscape)
        ->toBeTruthy()
        ->toBeString()
        ->toBeExists()
        ->and($landscape->width)
        ->toBe(400)
        ->and($landscape->height)
        ->toBe(226)
        ->and($landscape->duration)
        ->toBe(5)
        //
        ->and($upload->portrait)
        ->toBeTruthy()
        ->toBeString()
        ->toBeExists()
        ->and($portrait->width)
        ->toBe(712)
        ->and($portrait->height)
        ->toBe(400)
        ->and($portrait->duration)
        ->toBe(5)
        //
        ->and($upload->exact)
        ->toBeTruthy()
        ->toBeString()
        ->toBeExists()
        ->and($exact->width)
        ->toBe(300)
        ->and($exact->height)
        ->toBe(190)
        ->and($exact->duration)
        ->toBe(5)
        //
        ->and($upload->auto)
        ->toBeTruthy()
        ->toBeString()
        ->toBeExists()
        ->and($auto->width)
        ->toBe(300)
        ->and($auto->height)
        ->toBe(168)
        ->and($auto->duration)
        ->toBe(5);

});

it('will generate stream in standalone mode correctly', function() {
    $upload = Larupload::init('uploader')
        ->namingMethod(LaruploadNamingMethod::HASH_FILE)
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
            height: 720,
            format: (new X264)
                ->setKiloBitrate(1000)
                ->setAudioKiloBitrate(64)
        )
        ->upload(mp4());

    $baseUrl = url('/');
    $url = str_replace($baseUrl, '', $upload->stream);
    $path = public_path($url);
    $dir = pathinfo($path, PATHINFO_DIRNAME);

    $folders = ['480p', '720p'];

    expect(file_exists($path))->toBeTrue();

    foreach ($folders as $folder) {
        expect(file_exists($dir . '/' . $folder . '/' . "$folder-list.m3u8"))
            ->toBeTrue()
            ->and(file_exists($dir . '/' . $folder . '/' . "$folder-0.ts"))
            ->toBeTrue();
    }

});

it('will generate video styles correctly when secure-ids is enabled', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    config()->set('larupload.secure-ids', LaruploadSecureIdsMethod::ULID);

    $model = new ($model::class);
    $model->setAttachments(
        TestAttachmentBuilder::make($model->mode)->withLandscapeVideo()->toArray()
    );
    $model = save($model, mp4());

    $attachment = $model->attachment('main_file');
    $id = $attachment->meta('id');
    $cover = urlToVideo($attachment->url('cover'));
    $landscape = urlToVideo($attachment->url('landscape'));

    expect(Str::isUlid($id))->toBeTrue()
        //
        ->and($attachment->url('cover'))
        ->toBeTruthy()
        ->toBeString()
        ->toBeExists()
        ->and($cover->width)
        ->toBe(500)
        ->and($cover->height)
        ->toBe(500)
        ->and($cover->duration)
        ->toBe(0)
        //
        ->and($attachment->url('landscape'))
        ->toBeTruthy()
        ->toBeString()
        ->toBeExists()
        ->and($landscape->width)
        ->toBe(400)
        ->and($landscape->height)
        ->toBe(226)
        ->and($landscape->duration)
        ->toBe(5);

})->with('models');

it('will capture cover in resize mode [standalone]', function() {
    $model = LaruploadTestModels::HEAVY->instance();
    $model->setAttachments([
        Attachment::make('main_file')
            ->disk('local')
            ->withMeta(true)
            ->coverStyle('cover', 500, 500, LaruploadMediaStyle::AUTO)
    ]);

    $model = save($model, mp4());
    $attachment = $model->attachment('main_file');
    $cover = urlToImage($attachment->url('cover'));

    expect($cover->getSize()->getWidth())
        ->toBe(500)
        ->and($cover->getSize()->getHeight())
        ->toBe(282);
});
