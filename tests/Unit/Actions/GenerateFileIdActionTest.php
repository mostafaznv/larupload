<?php

use Hashids\Hashids;
use Mostafaznv\Larupload\Actions\GenerateFileIdAction;
use Mostafaznv\Larupload\Enums\LaruploadMode;
use Mostafaznv\Larupload\Enums\LaruploadSecureIdsMethod;
use Mostafaznv\Larupload\Test\Support\Enums\LaruploadTestModels;
use Illuminate\Support\Str;
use Sqids\Sqids;


beforeEach(function () {
    $this->model = LaruploadTestModels::HEAVY->instance();
    $this->method = LaruploadSecureIdsMethod::HASHID;
    $this->mode = LaruploadMode::HEAVY;
    $this->name = 'main_file';
});


it('returns existing secure id if present [heavy]', function () {
    $model = LaruploadTestModels::HEAVY->instance();
    $model->{"{$this->name}_file_id"} = 'existing-secure-id';

    $action = GenerateFileIdAction::make($model, $this->method, LaruploadMode::HEAVY, $this->name);
    $result = $action->run();

    expect($result)->toBe('existing-secure-id');
});

it('returns existing secure id if present [light]', function () {
    $model = LaruploadTestModels::LIGHT->instance();
    $model->{"{$this->name}_file_meta"} = json_encode([
        'id' => 'existing-secure-id',
    ]);

    $action = GenerateFileIdAction::make($model, $this->method, LaruploadMode::LIGHT, $this->name);
    $result = $action->run();

    expect($result)->toBe('existing-secure-id');
});

it('generates ulid when method is ULID', function () {
    Str::createUlidsUsing(function () {
        return new class {
            public function toBase32(): string
            {
                return '01FZ8Z7U5K6X5V7G5Y4Z8Z7U5K';
            }
        };
    });

    $action = new GenerateFileIdAction($this->model, LaruploadSecureIdsMethod::ULID, $this->mode, $this->name);
    $result = $action->run();

    expect($result)->toBe('01FZ8Z7U5K6X5V7G5Y4Z8Z7U5K');
});

it('generates uuid when method is UUID', function () {
    Str::createUuidsUsing(function () {
        return new class {
            public function toString(): string
            {
                return '9a5f2b20-f203-44b8-9fe2-53b21bfcd349';
            }
        };
    });

    $action = new GenerateFileIdAction($this->model, LaruploadSecureIdsMethod::UUID, $this->mode, $this->name);
    $result = $action->run();

    expect($result)->toBe('9a5f2b20-f203-44b8-9fe2-53b21bfcd349');
});

it('generates sqid when method is SQID', function () {
    $this->model->id = 4512;

    $action = new GenerateFileIdAction($this->model, LaruploadSecureIdsMethod::SQID, $this->mode, $this->name);
    $result = $action->run();

    $sqids = new Sqids(minLength: 20);
    $decoded = $sqids->decode($result);

    expect($decoded)->toBe([4512]);
});

it('generates sqid when method is HASHID', function () {
    $this->model->id = 841;

    $action = new GenerateFileIdAction($this->model, LaruploadSecureIdsMethod::HASHID, $this->mode, $this->name);
    $result = $action->run();

    $salt = config('app.key');
    $hashids = new Hashids($salt, 20);
    $decoded = $hashids->decode($result);

    expect($decoded)->toBe([841]);
});

it('returns model id when method is NONE', function () {
    $this->model->id = 42125;

    $action = new GenerateFileIdAction($this->model, LaruploadSecureIdsMethod::NONE, $this->mode, $this->name);
    $result = $action->run();

    expect($result)->toBe('42125');
});
