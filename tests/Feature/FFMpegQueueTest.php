<?php

use FFMpeg\Format\Video\X264;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Bus;
use Mostafaznv\Larupload\Enums\LaruploadSecureIdsMethod;
use Mostafaznv\Larupload\Events\LaruploadFFMpegQueueFinished;
use Mostafaznv\Larupload\Jobs\ProcessFFMpeg;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Storage;
use Mostafaznv\Larupload\Larupload;
use Mostafaznv\Larupload\Models\LaruploadFFMpegQueue;
use Mostafaznv\Larupload\Test\Support\Enums\LaruploadTestModels;

beforeEach(function() {
    Bus::fake();
    Event::fake(LaruploadFFMpegQueueFinished::class);

    config()->set('larupload.ffmpeg.queue', true);
});

it('will process ffmpeg through queue', function() {
    save(LaruploadTestModels::QUEUE->instance(), mp4());

    Bus::assertDispatched(ProcessFFMpeg::class);
});

it('will process ffmpeg through queue in standalone mode', function() {
    Larupload::init('uploader')
        ->video('landscape', 400)
        ->upload(mp4());

    save(LaruploadTestModels::QUEUE->instance(), mp4());

    Bus::assertDispatched(ProcessFFMpeg::class);
});

it('will create video styles through queue process', function() {
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

it('will create video styles through queue process in standalone mode', function() {
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

it('will create streams through queue process', function() {
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

it('will create streams through queue process in standalone mode', function() {
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

it('can queue ffmpeg for remote disks and deletes local files after finishing the process', function() {
    # init
    $disk = 's3';
    $localDisk = config()->get('larupload.local-disk');
    Storage::fake($disk);

    # save model
    $model = LaruploadTestModels::REMOTE_QUEUE->instance();
    $model = save($model, mp4());

    # prepare for assertions
    $queue = DB::table(Larupload::FFMPEG_QUEUE_TABLE)->first();
    $fileName = $model->attachment('main_file')->meta('name');
    $s3Files = Storage::disk($disk)->allFiles();
    $localFiles = Storage::disk($localDisk)->allFiles();

    # assertions 1
    expect($s3Files)
        ->toHaveCount(2)
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
        ->toHaveCount(7)
        ->and($localFiles)
        ->toHaveCount(0);
});

it('can queue ffmpeg for remote disks and deletes local files after finishing the process in standalone mode', function() {
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
});

it('can queue ffmpeg when using secure-ids', function() {
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

it('can queue ffmpeg when using secure-ids in standalone mode', function() {
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

it('will change queue status after processing queue', function() {
    $model = LaruploadTestModels::QUEUE->instance();
    $model = save($model, mp4());
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
});

it('will fire an event when process is finished', function() {
    $model = LaruploadTestModels::QUEUE->instance();
    $model = save($model, mp4());
    $queue = DB::table(Larupload::FFMPEG_QUEUE_TABLE)->first();

    Event::assertNotDispatched(LaruploadFFMpegQueueFinished::class);

    $process = new ProcessFFMpeg($queue->id, $model->id, 'main_file', $model::class);
    $process->handle();

    Event::assertDispatched(LaruploadFFMpegQueueFinished::class);
});

it('will update queue record whit error message, when process failed', function() {
    $model = LaruploadTestModels::QUEUE->instance();
    $model = save($model, mp4());
    $queue = DB::table(Larupload::FFMPEG_QUEUE_TABLE)->first();

    expect($queue->message)->toBeNull();

    rmRf(public_path('uploads'));

    $process = new ProcessFFMpeg($queue->id, $model->id, 'main_file', $model::class);
    $process->handle();

    $queue = DB::table(Larupload::FFMPEG_QUEUE_TABLE)->first();

    expect($queue->message)->toBeTruthy();

})->throws(Exception::class);

it('can load queue relationships of model', function() {
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

it('will throw exception if max-queue-num exceeds', function() {
    config()->set('larupload.ffmpeg.max-queue-num', 1);

    $model = LaruploadTestModels::QUEUE->instance();

    save($model, mp4());
    save($model, mp4());
    save($model, mp4());

})->throws(HttpResponseException::class);
