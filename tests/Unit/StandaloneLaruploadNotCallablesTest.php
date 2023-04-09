<?php

use Mostafaznv\Larupload\Enums\LaruploadSecureIdsMethod;
use Mostafaznv\Larupload\Larupload;
use Mostafaznv\Larupload\Test\Support\Enums\LaruploadTestModels;

$msg = 'This function is flagged as @internal and is not available on the standalone uploader.';

it('will throw exception when url method cals on standalone larupload facade', function () {
    Larupload::init('uploader')->url();

})->throws(Exception::class, $msg);

it('will throw exception when meta method cals on standalone larupload facade', function () {
    Larupload::init('uploader')->meta();

})->throws(Exception::class, $msg);

it('will throw exception when urls method cals on standalone larupload facade', function () {
    Larupload::init('uploader')->urls();

})->throws(Exception::class, $msg);

it('will throw exception when handleFFMpegQueue method cals on standalone larupload facade', function () {
    Larupload::init('uploader')->handleFFMpegQueue();

})->throws(Exception::class, $msg);

it('will throw exception when saved method cals on standalone larupload facade', function () {
    Larupload::init('uploader')->saved(LaruploadTestModels::HEAVY->instance());

})->throws(Exception::class, $msg);

it('will throw exception when deleted method cals on standalone larupload facade', function () {
    Larupload::init('uploader')->deleted();

})->throws(Exception::class, $msg);

it('will throw exception when download method cals on standalone larupload facade', function () {
    Larupload::init('uploader')->download();

})->throws(Exception::class, $msg);

it('will throw exception when setOutput method cals on standalone larupload facade', function () {
    Larupload::init('uploader')->setOutput(LaruploadTestModels::HEAVY->instance());

})->throws(Exception::class, $msg);

it('will throw exception when secureIdsMethod method cals on standalone larupload facade', function () {
    Larupload::init('uploader')->secureIdsMethod(LaruploadSecureIdsMethod::ULID);

})->throws(Exception::class, $msg);

it('will throw exception when updateCover method cals on standalone larupload facade', function () {
    Larupload::init('uploader')->updateCover(jpg());

})->throws(Exception::class, $msg);
