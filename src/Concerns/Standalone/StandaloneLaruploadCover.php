<?php

namespace Mostafaznv\Larupload\Concerns\Standalone;

use Illuminate\Http\UploadedFile;

trait StandaloneLaruploadCover
{
    /**
     * @internal
     */
    public function updateCover(UploadedFile $file): bool
    {
        if ($this->internalFunctionIsCallable) {
            return parent::updateCover($file);
        }

        $this->internalException();
    }

    public function changeCover(UploadedFile $file): ?object
    {
        if ($this->metaExists()) {
            $this->internalFunctionIsCallable = true;
            $res = $this->updateCover($file);

            if ($res) {
                $this->setCover($this->id);
                $this->updateMeta();

                return $this->urls();
            }
        }

        return null;
    }

    public function deleteCover(): ?object
    {
        if ($this->metaExists()) {
            $this->internalFunctionIsCallable = true;
            $res = parent::detachCover();

            if ($res) {
                $this->setCover($this->id);
                $this->updateMeta();

                return $this->urls();
            }
        }

        return null;
    }

    /**
     * @internal
     */
    public function detachCover(): bool
    {
        if ($this->internalFunctionIsCallable) {
            return parent::detachCover();
        }

        $this->internalException();
    }
}
