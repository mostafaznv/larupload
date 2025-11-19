<?php

namespace Mostafaznv\Larupload\Actions;

use Illuminate\Support\Facades\Storage;
use Mostafaznv\Larupload\Storage\Attachment;


class RetrieveStandaloneMetaFileAction
{
    public function __invoke(Attachment $attachment): ?array
    {
        $metaPath = larupload_relative_path($attachment, $attachment->id) . '/.meta';

        if (Storage::disk($attachment->disk)->exists($metaPath)) {
            $meta = Storage::disk($attachment->disk)->get($metaPath);
            $meta = json_decode($meta);

            if (property_exists($meta, 'meta')) {
                $output = $attachment->output;

                foreach ($meta->meta as $key => $value) {
                    $output[$key] = $value;
                }

                return $output;
            }
        }

        return null;
    }
}
