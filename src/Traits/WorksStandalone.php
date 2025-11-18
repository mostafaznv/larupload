<?php

namespace Mostafaznv\Larupload\Traits;

use Illuminate\Support\Facades\Storage;
use Mostafaznv\Larupload\Storage\Attachment;


trait WorksStandalone
{
    private function updateMeta(Attachment $attachment, ?object $urls = null): void
    {
        if (is_null($urls)) {
            $urls = $attachment->urls();
        }

        $metaPath = larupload_relative_path($attachment, $attachment->id) . '/.meta';

        Storage::disk($attachment->disk)->put($metaPath, json_encode($urls), 'private');
    }
}
