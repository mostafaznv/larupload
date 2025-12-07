<?php

namespace Mostafaznv\Larupload\Actions\Attachment;

use Mostafaznv\Larupload\Actions\HandleStylesAction;
use Mostafaznv\Larupload\Larupload;
use Mostafaznv\Larupload\Traits\WorksStandalone;


class SaveStandaloneAttachmentAction extends StoreAttachmentAction
{
    use WorksStandalone;

    public function execute(): object
    {
        $this->clean();
        $this->basic();
        $this->media();
        $this->uploadOriginalFile($this->attachment->id);
        $this->setCover($this->attachment->id);

        $urls = $this->updateMeta($this->attachment);

        HandleStylesAction::make($this->attachment)->run($this->attachment->id, Larupload::class, true);

        return $urls;
    }
}
