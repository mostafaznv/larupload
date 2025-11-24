<?php

namespace Mostafaznv\Larupload\Actions;

use Illuminate\Support\Facades\Storage;
use Mostafaznv\Larupload\DTOs\Style\Output;
use Mostafaznv\Larupload\Storage\Attachment;


class RetrieveStandaloneMetaFileAction
{
    public function __invoke(Attachment $attachment): ?Output
    {
        $metaPath = larupload_relative_path($attachment, $attachment->id) . '/.meta';

        if (Storage::disk($attachment->disk)->exists($metaPath)) {
            $meta = Storage::disk($attachment->disk)->get($metaPath);
            $meta = json_decode($meta);

            if (property_exists($meta, 'meta')) {
                $output = $attachment->output;

                foreach ($meta->meta as $key => $value) {
                    $output->set($key, $value);
                }

                return $output;
            }
        }

        return null;
    }
}
