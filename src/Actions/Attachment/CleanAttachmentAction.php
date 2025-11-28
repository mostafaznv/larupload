<?php

namespace Mostafaznv\Larupload\Actions\Attachment;

use Illuminate\Support\Facades\Storage;
use Mostafaznv\Larupload\DTOs\Style\Output;
use Mostafaznv\Larupload\Storage\Attachment;


class CleanAttachmentAction
{
    public function __invoke(Attachment $attachment): Attachment
    {
        $path = larupload_relative_path($attachment, $attachment->id);
        Storage::disk($attachment->disk)->deleteDirectory($path);

        $attachment->output = Output::make();

        return $attachment;
    }
}
