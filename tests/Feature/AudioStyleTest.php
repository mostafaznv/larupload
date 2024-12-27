<?php

use FFMpeg\Format\Audio\Flac;
use FFMpeg\Format\Audio\Mp3;
use FFMpeg\Format\Audio\Wav;
use Mostafaznv\Larupload\Larupload;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadHeavyTestModel;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadLightTestModel;
use Mostafaznv\Larupload\Enums\LaruploadSecureIdsMethod;
use Illuminate\Support\Str;
use Mostafaznv\Larupload\Test\Support\TestAttachmentBuilder;


it('will generate audio styles correctly', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    $model->withAllAudios();
    $model = save($model, mp3());

    $attachment = $model->attachment('main_file');
    $mp3 = urlToAudio($attachment->url('audio_mp3'));
    $wav = urlToAudio($attachment->url('audio_wav'));
    $flac = urlToAudio($attachment->url('audio_flac'));

    expect($attachment->url('cover'))
        ->toBeNull()
        // mp3
        ->and($attachment->url('audio_mp3'))
        ->toBeTruthy()
        ->toBeString()
        ->toBeExists()
        ->and($mp3->width)
        ->toBeNull()
        ->and($mp3->height)
        ->toBeNull()
        ->and($mp3->duration)
        ->toBe(67)
        // wav
        ->and($attachment->url('audio_wav'))
        ->toBeTruthy()
        ->toBeString()
        ->toBeExists()
        ->and($wav->width)
        ->toBeNull()
        ->and($wav->height)
        ->toBeNull()
        ->and($wav->duration)
        ->toBe(67)
        // flac
        ->and($attachment->url('audio_flac'))
        ->toBeTruthy()
        ->toBeString()
        ->toBeExists()
        ->and($flac->width)
        ->toBeNull()
        ->and($flac->height)
        ->toBeNull()
        ->and($flac->duration)
        ->toBe(67);

})->with('models');

it('will generate audio styles in standalone mode correctly', function() {
    $upload = Larupload::init('uploader')
        ->audio('audio_mp3', new Mp3())
        ->audio('audio_wav', new Wav())
        ->audio('audio_flac', new Flac())
        ->upload(mp3());

    $mp3 = urlToVideo($upload->audio_mp3);
    $wav = urlToVideo($upload->audio_wav);
    $flac = urlToVideo($upload->audio_flac);

    expect($upload->cover)
        ->toBeNull()
        // mp3
        ->and($upload->audio_mp3)
        ->toBeTruthy()
        ->toBeString()
        ->toBeExists()
        ->and($mp3->width)
        ->toBeNull()
        ->and($mp3->height)
        ->toBeNull()
        ->and($mp3->duration)
        ->toBe(67)
        // wav
        ->and($upload->audio_wav)
        ->toBeTruthy()
        ->toBeString()
        ->toBeExists()
        ->and($wav->width)
        ->toBeNull()
        ->and($wav->height)
        ->toBeNull()
        ->and($wav->duration)
        ->toBe(67)
        // flac
        ->and($upload->audio_flac)
        ->toBeTruthy()
        ->toBeString()
        ->toBeExists()
        ->and($flac->width)
        ->toBeNull()
        ->and($flac->height)
        ->toBeNull()
        ->and($flac->duration)
        ->toBe(67);
});

it('will generate audio styles correctly when secure-ids is enabled', function(LaruploadHeavyTestModel|LaruploadLightTestModel $model) {
    config()->set('larupload.secure-ids', LaruploadSecureIdsMethod::ULID);

    $model = new ($model::class);
    $model->setAttachments(
        TestAttachmentBuilder::make($model->mode)->withWavAudio()->toArray()
    );
    $model = save($model, mp3());

    $attachment = $model->attachment('main_file');
    $id = $attachment->meta('id');
    $wav = urlToVideo($attachment->url('audio_wav'));

    expect(Str::isUlid($id))->toBeTrue()
        // cover
        ->and($attachment->url('cover'))
        ->toBeNull()
        // wav
        ->and($attachment->url('audio_wav'))
        ->toBeTruthy()
        ->toBeString()
        ->toBeExists()
        ->and($wav->width)
        ->toBeNull()
        ->and($wav->height)
        ->toBeNull()
        ->and($wav->duration)
        ->toBe(67);

})->with('models');
