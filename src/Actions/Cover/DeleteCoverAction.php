<?php

namespace Mostafaznv\Larupload\Actions\Cover;

use Mostafaznv\Larupload\DTOs\Style\Output;
use Mostafaznv\Larupload\Enums\LaruploadFileType;


class DeleteCoverAction
{
    public function __construct(
        private readonly ?LaruploadFileType $type,
        protected Output                    $output
    ) {}

    public static function make(?LaruploadFileType $type, Output $output): self
    {
        return new self($type, $output);
    }


    public function run(): Output
    {
        $this->output->cover = null;

        if ($this->type != LaruploadFileType::IMAGE) {
            $this->output->dominantColor = null;
        }

        return $this->output;
    }
}
