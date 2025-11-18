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

        HandleStylesAction::make($this->attachment)->run($this->attachment->id, Larupload::class, true);

        $urls = $this->attachment->urls();
        $this->updateMeta($this->attachment, $urls);

        return $urls;
    }
}
