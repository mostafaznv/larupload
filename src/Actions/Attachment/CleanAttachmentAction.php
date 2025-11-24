<?php

namespace Mostafaznv\Larupload\Actions\Attachment;

use Illuminate\Support\Facades\Storage;
use Mostafaznv\Larupload\DTOs\Style\Output;
use Mostafaznv\Larupload\Storage\Attachment;


class CleanAttachmentAction
{
    public function __invoke(Attachment $attachment, string|int|null $id = null): Attachment
    {
        if (is_null($id)) {
            $id = $attachment->id;
        }

        $path = larupload_relative_path($attachment, $id);
        Storage::disk($attachment->disk)->deleteDirectory($path);

        $attachment->output = Output::make();

        return $attachment;
    }
}
