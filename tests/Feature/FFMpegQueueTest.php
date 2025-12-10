<?php

use FFMpeg\Format\Audio\Wav;
use FFMpeg\Format\Video\X264;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Bus;
use Mostafaznv\Larupload\Enums\LaruploadSecureIdsMethod;
use Mostafaznv\Larupload\Events\LaruploadFFMpegQueueFinished;
use Mostafaznv\Larupload\Exceptions\FFMpegQueueMaxNumExceededException;
use Mostafaznv\Larupload\Jobs\ProcessFFMpeg;
use Illuminate\Support\Facades\Storage;
use Mostafaznv\Larupload\Larupload;
use Mostafaznv\Larupload\Models\LaruploadFFMpegQueue;
use Mostafaznv\Larupload\Test\Support\Enums\LaruploadTestModels;
use Mostafaznv\Larupload\Test\Support\Models\LaruploadQueueTestModel;


beforeEach(function () {
    Bus::fake();
    Queue::fake();
    Event::fake(LaruploadFFMpegQueueFinished::class);

    config()->set('larupload.ffmpeg.queue', true);
});


it('will process ffmpeg through queue', function (UploadedFile $file) {
    Bus::assertNotDispatched(ProcessFFMpeg::class);

    save(LaruploadTestModels::QUEUE->instance(), $file);

    Bus::assertDispatched(ProcessFFMpeg::class);

})->with([
    'mp4' => fn() => mp4(),
    'mp3' => fn() => mp3(),
]);

it('will process ffmpeg through queue in standalone mode [video]', function () {
    Bus::assertNotDispatched(ProcessFFMpeg::class);

    Larupload::init('uploader')
        ->video('landscape', 400)
        ->upload(mp4());

    Bus::assertDispatched(ProcessFFMpeg::class);
});

it('will process ffmpeg through queue in standalone mode [audio]', function () {
    Bus::assertNotDispatched(ProcessFFMpeg::class);

    Larupload::init('uploader')
        ->audio('audio_wav', new Wav())
        ->upload(mp3());

    Bus::assertDispatched(ProcessFFMpeg::class);
});

it('will create video styles through queue process', function () {
    $model = LaruploadTestModels::QUEUE->instance();
    $model = save($model, mp4());

    $urls = $model->attachment('main_file')->urls();

    expect($urls->original)
        ->toBeExists()
        ->and($urls->cover)
        ->toBeExists()
        ->and($urls->landscape)
        ->toNotExists();

    $queue = DB::table(Larupload::FFMPEG_QUEUE_TABLE)->first();
    $process = new ProcessFFMpeg($queue->id, $model->id, 'main_file', $model::class);
    $process->handle();

    expect($urls->landscape)->toBeExists();
});

it('will create audio styles through queue process', function () {
    $model = LaruploadTestModels::QUEUE->instance();
    $model = save($model, mp3());

    $urls = $model->attachment('main_file')->urls();

    expect($urls->original)
        ->toBeExists()
        ->and($urls->cover)
        ->toBeNull()
        ->and($urls->audio_wav)
        ->toNotExists();

    $queue = DB::table(Larupload::FFMPEG_QUEUE_TABLE)->first();
    $process = new ProcessFFMpeg($queue->id, $model->id, 'main_file', $model::class);
    $process->handle();

    expect($urls->audio_wav)->toBeExists();
});

it('will create video styles through queue process in standalone mode', function () {
    $name = 'uploader';
    $standalone = Larupload::init($name)->video('landscape', 400);
    $uploader = $standalone->upload(mp4());

    expect($uploader->original)
        ->toBeExists()
        ->and($uploader->cover)
        ->toBeExists()
        ->and($uploader->landscape)
        ->toNotExists();

    $queue = DB::table(Larupload::FFMPEG_QUEUE_TABLE)->first();
    $serializedClass = base64_encode(serialize($standalone));
    $process = new ProcessFFMpeg($queue->id, $uploader->meta->id, $name, Larupload::class, $serializedClass);
    $process->handle();

    expect($uploader->landscape)->toBeExists();
});

it('will create audio styles through queue process in standalone mode', function () {
    $name = 'uploader';
    $standalone = Larupload::init($name)->audio('audio_wav', new Wav);
    $uploader = $standalone->upload(mp3());

    expect($uploader->original)
        ->toBeExists()
        ->and($uploader->cover)
        ->toBeNull()
        ->and($uploader->audio_wav)
        ->toNotExists();

    $queue = DB::table(Larupload::FFMPEG_QUEUE_TABLE)->first();
    $serializedClass = base64_encode(serialize($standalone));
    $process = new ProcessFFMpeg($queue->id, $uploader->meta->id, $name, Larupload::class, $serializedClass);
    $process->handle();

    expect($uploader->audio_wav)->toBeExists();
});

it('will create streams through queue process', function () {
    $model = LaruploadTestModels::QUEUE->instance();
    $model = save($model, mp4());
    $queue = DB::table(Larupload::FFMPEG_QUEUE_TABLE)->first();

    $process = new ProcessFFMpeg($queue->id, $model->id, 'main_file', $model::class);
    $process->handle();

    $baseUrl = url('/');
    $url = str_replace($baseUrl, '', $model->attachment('main_file')->url('stream'));
    $path = public_path($url);
    $dir = pathinfo($path, PATHINFO_DIRNAME);

    expect(file_exists($path))->toBeTrue()
        ->and(file_exists("$dir/480p/480p-list.m3u8"))
        ->toBeTrue()
        ->and(file_exists("$dir/480p/480p-0.ts"))
        ->toBeTrue();

});

it('will create streams through queue process in standalone mode', function () {
    $name = 'uploader';
    $standalone = Larupload::init($name)->stream('480p', 640, 480, new X264);
    $uploader = $standalone->upload(mp4());

    $queue = DB::table(Larupload::FFMPEG_QUEUE_TABLE)->first();
    $serializedClass = base64_encode(serialize($standalone));

    $process = new ProcessFFMpeg($queue->id, $uploader->meta->id, $name, Larupload::class, $serializedClass);
    $process->handle();

    $baseUrl = url('/');
    $url = str_replace($baseUrl, '', $uploader->stream);
    $path = public_path($url);
    $dir = pathinfo($path, PATHINFO_DIRNAME);

    expect(file_exists($path))->toBeTrue()
        ->and(file_exists("$dir/480p/480p-list.m3u8"))
        ->toBeTrue()
        ->and(file_exists("$dir/480p/480p-0.ts"))
        ->toBeTrue();

});

it('can queue ffmpeg for remote disks and deletes local files after finishing the process', function (UploadedFile $file, int $expectedS3Files1, int $expectedS3Files2) {
    # init
    $disk = 's3';
    $localDisk = config()->get('larupload.local-disk');
    Storage::fake($disk);

    # save model
    $model = LaruploadTestModels::REMOTE_QUEUE->instance();
    $model = save($model, $file);

    # prepare for assertions
    $queue = DB::table(Larupload::FFMPEG_QUEUE_TABLE)->first();
    $fileName = $model->attachment('main_file')->meta('name');
    $s3Files = Storage::disk($disk)->allFiles();
    $localFiles = Storage::disk($localDisk)->allFiles();

    # assertions 1
    expect($s3Files)
        ->toHaveCount($expectedS3Files1)
        ->and($localFiles)
        ->toHaveCount(1)
        ->and($localFiles[0])
        ->toEndWith($fileName);


    # run queue
    $process = new ProcessFFMpeg($queue->id, $model->id, 'main_file', $model::class);
    $process->handle();

    # prepare for assertions
    $s3Files = Storage::disk($disk)->allFiles();
    $localFiles = Storage::disk($localDisk)->allFiles();

    // assertions 2
    expect($s3Files)
        ->toHaveCount($expectedS3Files2)
        ->and($localFiles)
        ->toHaveCount(0);

})->with([
    'mp4' => fn() => [mp4(), 2, 7],
    'mp3' => fn() => [mp3(), 1, 2],
]);

it('can queue ffmpeg for remote disks and deletes local files after finishing the process in standalone mode [video]', function () {
    # init
    $name = 'uploader';
    $disk = 's3';
    $localDisk = config()->get('larupload.local-disk');
    Storage::fake($disk);
    $standalone = Larupload::init($name)
        ->disk($disk)
        ->video('landscape', 400)
        ->stream('480p', 640, 480, new X264);

    # upload
    $uploader = $standalone->upload(mp4());

    # prepare for assertions
    $queue = DB::table(Larupload::FFMPEG_QUEUE_TABLE)->first();
    $serializedClass = base64_encode(serialize($standalone));
    $s3Files = Storage::disk($disk)->allFiles();
    $localFiles = Storage::disk($localDisk)->allFiles();

    # assertions 1
    expect($s3Files)
        ->toHaveCount(3)
        ->and($localFiles)
        ->toHaveCount(1)
        ->and($localFiles[0])
        ->toEndWith($uploader->meta->name);


    # run queue
    $process = new ProcessFFMpeg($queue->id, $uploader->meta->id, $name, Larupload::class, $serializedClass);
    $process->handle();

    # prepare for assertions
    $s3Files = Storage::disk($disk)->allFiles();
    $localFiles = Storage::disk($localDisk)->allFiles();

    // assertions 2
    expect($s3Files)
        ->toHaveCount(8)
        ->and($localFiles)
        ->toHaveCount(0);

    Storage::disk($disk)->deleteDirectory('/');
});

it('can queue ffmpeg for remote disks and deletes local files after finishing the process in standalone mode [audio]', function () {
    # init
    $name = 'uploader';
    $disk = 's3';
    $localDisk = config()->get('larupload.local-disk');
    Storage::fake($disk);
    $standalone = Larupload::init($name)
        ->disk($disk)
        ->audio('audio_wav', new Wav);

    # upload
    $uploader = $standalone->upload(mp3());

    # prepare for assertions
    $queue = DB::table(Larupload::FFMPEG_QUEUE_TABLE)->first();
    $serializedClass = base64_encode(serialize($standalone));
    $s3Files = Storage::disk($disk)->allFiles();
    $localFiles = Storage::disk($localDisk)->allFiles();


    # assertions 1
    expect($s3Files)
        ->toHaveCount(2)
        ->and($localFiles)
        ->toHaveCount(1)
        ->and($localFiles[0])
        ->toEndWith($uploader->meta->name);


    # run queue
    $process = new ProcessFFMpeg($queue->id, $uploader->meta->id, $name, Larupload::class, $serializedClass);
    $process->handle();

    # prepare for assertions
    $s3Files = Storage::disk($disk)->allFiles();
    $localFiles = Storage::disk($localDisk)->allFiles();

    // assertions 2
    expect($s3Files)
        ->toHaveCount(3)
        ->and($localFiles)
        ->toHaveCount(0);


    Storage::disk($disk)->deleteDirectory('/');
});

it('can queue ffmpeg when using secure-ids [video]', function () {
    config()->set('larupload.secure-ids', LaruploadSecureIdsMethod::ULID);

    $model = LaruploadTestModels::QUEUE->instance();
    $model = save($model, mp4());

    $urls = $model->attachment('main_file')->urls();

    expect($urls->original)
        ->toBeExists()
        ->and($urls->cover)
        ->toBeExists()
        ->and($urls->landscape)
        ->toNotExists();

    $queue = DB::table(Larupload::FFMPEG_QUEUE_TABLE)->first();
    $process = new ProcessFFMpeg($queue->id, $model->id, 'main_file', $model::class);
    $process->handle();

    expect($urls->landscape)->toBeExists();
});

it('can queue ffmpeg when using secure-ids [audio]', function () {
    config()->set('larupload.secure-ids', LaruploadSecureIdsMethod::ULID);

    $model = LaruploadTestModels::QUEUE->instance();
    $model = save($model, mp3());

    $urls = $model->attachment('main_file')->urls();

    expect($urls->original)
        ->toBeExists()
        ->and($urls->cover)
        ->toBeExists()
        ->and($urls->audio_wav)
        ->toNotExists();

    $queue = DB::table(Larupload::FFMPEG_QUEUE_TABLE)->first();
    $process = new ProcessFFMpeg($queue->id, $model->id, 'main_file', $model::class);
    $process->handle();

    expect($urls->audio_wav)->toBeExists();
});

it('can queue ffmpeg when using secure-ids in standalone mode [video]', function () {
    config()->set('larupload.secure-ids', LaruploadSecureIdsMethod::ULID);

    $name = 'uploader';
    $standalone = Larupload::init($name)->video('landscape', 400);
    $uploader = $standalone->upload(mp4());

    expect($uploader->original)
        ->toBeExists()
        ->and($uploader->cover)
        ->toBeExists()
        ->and($uploader->landscape)
        ->toNotExists();

    $queue = DB::table(Larupload::FFMPEG_QUEUE_TABLE)->first();
    $serializedClass = base64_encode(serialize($standalone));
    $process = new ProcessFFMpeg($queue->id, $uploader->meta->id, $name, Larupload::class, $serializedClass);
    $process->handle();

    expect($uploader->landscape)->toBeExists();


    $model = LaruploadTestModels::QUEUE->instance();
    $model = save($model, mp4());

    $urls = $model->attachment('main_file')->urls();

    expect($urls->original)
        ->toBeExists()
        ->and($urls->cover)
        ->toBeExists()
        ->and($urls->landscape)
        ->toNotExists();

    $queue = DB::table(Larupload::FFMPEG_QUEUE_TABLE)->first();
    $process = new ProcessFFMpeg($queue->id, $model->id, 'main_file', $model::class);
    $process->handle();

    expect($urls->landscape)->toBeExists();
});

it('can queue ffmpeg when using secure-ids in standalone mode [audio]', function () {
    config()->set('larupload.secure-ids', LaruploadSecureIdsMethod::ULID);

    $name = 'uploader';
    $standalone = Larupload::init($name)->audio('audio_wav', new Wav);
    $uploader = $standalone->upload(mp3());

    expect($uploader->original)
        ->toBeExists()
        ->and($uploader->cover)
        ->toBeExists()
        ->and($uploader->audio_wav)
        ->toNotExists();

    $queue = DB::table(Larupload::FFMPEG_QUEUE_TABLE)->first();
    $serializedClass = base64_encode(serialize($standalone));
    $process = new ProcessFFMpeg($queue->id, $uploader->meta->id, $name, Larupload::class, $serializedClass);
    $process->handle();

    expect($uploader->audio_wav)->toBeExists();


    $model = LaruploadTestModels::QUEUE->instance();
    $model = save($model, mp3());

    $urls = $model->attachment('main_file')->urls();

    expect($urls->original)
        ->toBeExists()
        ->and($urls->cover)
        ->toBeExists()
        ->and($urls->audio_wav)
        ->toNotExists();

    $queue = DB::table(Larupload::FFMPEG_QUEUE_TABLE)->first();
    $process = new ProcessFFMpeg($queue->id, $model->id, 'main_file', $model::class);
    $process->handle();

    expect($urls->audio_wav)->toBeExists();
});

it('will change queue status after processing queue', function (UploadedFile $file) {
    $model = LaruploadTestModels::QUEUE->instance();
    $model = save($model, $file);
    $queue = DB::table(Larupload::FFMPEG_QUEUE_TABLE)->first();

    expect($queue)
        ->toBeObject()
        ->and($queue->status)
        ->toBe(0);


    # run queue
    $process = new ProcessFFMpeg($queue->id, $model->id, 'main_file', $model::class);
    $process->handle();

    $queue = DB::table(Larupload::FFMPEG_QUEUE_TABLE)->first();

    expect($queue)
        ->toBeObject()
        ->and($queue->status)
        ->toBe(1);

})->with([
    'mp4' => fn() => mp4(),
    'mp3' => fn() => mp3(),
]);

it('will fire an event when process is finished', function (UploadedFile $file) {
    $model = LaruploadTestModels::QUEUE->instance();
    $model = save($model, $file);
    $queue = DB::table(Larupload::FFMPEG_QUEUE_TABLE)->first();

    Event::assertNotDispatched(LaruploadFFMpegQueueFinished::class);

    $process = new ProcessFFMpeg($queue->id, $model->id, 'main_file', $model::class);
    $process->handle();

    Event::assertDispatched(LaruploadFFMpegQueueFinished::class);

})->with([
    'mp4' => fn() => mp4(),
    'mp3' => fn() => mp3(),
]);

it('will update queue record with error message, when process failed', function (UploadedFile $file) {
    $model = LaruploadTestModels::QUEUE->instance();
    $model = save($model, $file);
    $queue = DB::table(Larupload::FFMPEG_QUEUE_TABLE)->first();

    expect($queue->message)->toBeNull();

    rmRf(public_path('uploads'));

    $process = new ProcessFFMpeg($queue->id, $model->id, 'main_file', $model::class);
    $process->handle();

    $queue = DB::table(Larupload::FFMPEG_QUEUE_TABLE)->first();

    expect($queue->message)->toBeTruthy();

})->throws(Exception::class)->with([
    'mp4' => fn() => mp4(),
    'mp3' => fn() => mp3(),
]);

it('can load queue relationships of model', function () {
    $model = LaruploadTestModels::QUEUE->instance();
    $model->load('laruploadQueue', 'laruploadQueues');
    $model = save($model, mp4());

    expect($model->laruploadQueue)
        ->toBeInstanceOf(LaruploadFFMpegQueue::class)
        ->id->toBe(1)
        ->status->toBe(0)
        ->message->toBeNull()
        ->and($model->laruploadQueues)
        ->toBeInstanceOf(Collection::class)
        ->toHaveCount(1)
        ->and($model->laruploadQueues[0])
        ->toBeInstanceOf(LaruploadFFMpegQueue::class);
});

it('will throw exception if max-queue-num exceeds', function () {
    config()->set('larupload.ffmpeg.max-queue-num', 1);

    $model = LaruploadTestModels::QUEUE->instance();

    save($model, mp4());
    save($model, mp3());
    save($model, mp3());

})->throws(FFMpegQueueMaxNumExceededException::class, 'larupload queue limitation exceeded.');

it('will throw an exception if model/file does not exist', function () {
    $model = LaruploadTestModels::QUEUE->instance();
    $model = save($model, mp3());


    LaruploadQueueTestModel::where('id', $model->id)->delete();


    $queue = DB::table(Larupload::FFMPEG_QUEUE_TABLE)->first();
    $process = new ProcessFFMpeg($queue->id, $model->id, 'main_file', $model::class);
    $process->handle();

    expect(true)->toBeFalse();

})->throws(Exception::class, 'File/Model not found for FFMpeg processing.');
