<?php

namespace Mostafaznv\Larupload\Concerns;

use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Storage;
use Mostafaznv\Larupload\Actions\Attachment\SaveAttachmentAction;
use Mostafaznv\Larupload\Actions\HandleModelStylesAction;


trait LaruploadObservers
{
    protected static function boot(): void
    {
        parent::boot();

        static::saved(function ($model) {
            $shouldSave = false;

            foreach ($model->attachments as $attachment) {
                if (!$attachment->isUploaded()) {
                    $shouldSave = true;

                    $model = SaveAttachmentAction::make($attachment)->execute($model);
                }
            }

            if ($shouldSave) {
                $model->save();

                resolve(HandleModelStylesAction::class)($model, $model->attachments);
            }
        });

        static::deleted(function ($model) {
            if (!$model->hasGlobalScope(SoftDeletingScope::class) or $model->isForceDeleting()) {
                foreach ($model->attachments as $attachment) {
                    if (!$attachment->preserveFiles) {
                        Storage::disk($attachment->disk)->deleteDirectory("$attachment->folder/$attachment->id");
                    }
                }
            }
        });

        static::retrieved(function ($model) {
            foreach ($model->attachments as $attachment) {
                $attachment->setOutput($model);
            }
        });
    }
}
