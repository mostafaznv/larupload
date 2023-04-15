<?php

namespace Mostafaznv\Larupload\Actions\Cover;

use Mostafaznv\Larupload\Enums\LaruploadFileType;

class DeleteCoverAction
{
    public function __construct(
        private readonly ?LaruploadFileType $type,
        protected array                     $output
    ) {}

    public static function make(?LaruploadFileType $type, array $output): self
    {
        return new self($type, $output);
    }


    public function run(): array
    {
        $this->output['cover'] = null;

        if ($this->type != LaruploadFileType::IMAGE) {
            $this->output['dominant_color'] = null;
        }

        return $this->output;
    }
}
