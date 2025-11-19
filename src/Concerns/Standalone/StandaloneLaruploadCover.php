<?php

namespace Mostafaznv\Larupload\Concerns\Standalone;

use Illuminate\Http\UploadedFile;
use Mostafaznv\Larupload\Actions\RetrieveStandaloneMetaFileAction;
use Mostafaznv\Larupload\Actions\UpdateStandaloneMetaFileAction;


trait StandaloneLaruploadCover
{
    public function changeCover(UploadedFile $file): ?object
    {
        $output = resolve(RetrieveStandaloneMetaFileAction::class)($this);

        if ($output) {
            $this->output = $output;
            $this->internalFunctionIsCallable = true;

            $res = $this->updateCover($file);

            if ($res) {
                $this->setCover($this->id);
                $urls = $this->urls();

                resolve(UpdateStandaloneMetaFileAction::class)($this, $urls);

                return $urls;
            }
        }

        return null;
    }

    public function deleteCover(): ?object
    {
        $output = resolve(RetrieveStandaloneMetaFileAction::class)($this);

        if ($output) {
            $this->output = $output;
            $this->internalFunctionIsCallable = true;

            $res = $this->detachCover();

            if ($res) {
                $this->setCover($this->id);
                $urls = $this->urls();

                resolve(UpdateStandaloneMetaFileAction::class)($this, $urls);

                return $this->urls();
            }
        }

        return null;
    }


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
