<?php

namespace Mostafaznv\Larupload\Concerns\Standalone;

use Exception;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\UploadedFile;

trait StandaloneLaruploadCover
{
    /**
     * @param UploadedFile $file
     * @return bool
     * @throws Exception
     * @internal
     */
    public function updateCover(UploadedFile $file): bool
    {
        if ($this->internalFunctionIsCallable) {
            return parent::updateCover($file);
        }

        self::internalException();
    }

    /**
     * Update Cover
     *
     * @param UploadedFile $file
     * @return object|null
     * @throws FileNotFoundException
     * @throws Exception
     */
    public function changeCover(UploadedFile $file): ?object
    {
        if ($this->metaIsExists()) {
            $this->internalFunctionIsCallable = true;
            $res = parent::updateCover($file);

            if ($res) {
                $this->setCover($this->id);
                $this->updateMeta();

                return $this->urls();
            }
        }

        return null;
    }

    /**
     * Delete Cover
     *
     * @return object|null
     * @throws FileNotFoundException
     * @throws Exception
     */
    public function deleteCover(): ?object
    {
        if ($this->metaIsExists()) {
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
     * @return bool
     * @throws Exception
     * @internal
     */
    public function detachCover(): bool
    {
        if ($this->internalFunctionIsCallable) {
            return parent::detachCover();
        }

        self::internalException();
    }
}
