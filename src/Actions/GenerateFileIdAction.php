<?php

namespace Mostafaznv\Larupload\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Mostafaznv\Larupload\Enums\LaruploadMode;
use Mostafaznv\Larupload\Enums\LaruploadSecureIdsMethod;

class GenerateFileIdAction
{
    public function __construct(
        private readonly ?Model                   $model,
        private readonly LaruploadSecureIdsMethod $method,
        private readonly LaruploadMode            $attachmentMode,
        private readonly ?string                  $attachmentName
    ) {}

    public static function make(?Model $model, LaruploadSecureIdsMethod $method, LaruploadMode $attachmentMode, string $attachmentName): self
    {
        return new self($model, $method, $attachmentMode, $attachmentName);
    }


    public function run(): string
    {
        if ($secureId = $this->retrieveCurrentSecureId()) {
            return $secureId;
        }

        return match ($this->method) {
            LaruploadSecureIdsMethod::ULID   => Str::ulid()->toBase32(),
            LaruploadSecureIdsMethod::UUID   => Str::uuid()->toString(),
            LaruploadSecureIdsMethod::SQID   => $this->sqid(),
            default                          => $this->model->id,
        };
    }

    private function retrieveCurrentSecureId(): string|null
    {
        if ($this->attachmentMode === LaruploadMode::HEAVY) {
            return $this->model->{"{$this->attachmentName}_file_id"} ?? null;
        }

        return json_decode($this->model->{"{$this->attachmentName}_file_meta"})->id ?? null;
    }

    private function sqid(): string
    {
        $sqids = new \Sqids\Sqids(minLength: 20);

        return $sqids->encode([$this->model->id]);
    }
}
