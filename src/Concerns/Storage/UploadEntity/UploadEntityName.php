<?php

namespace Mostafaznv\Larupload\Concerns\Storage\UploadEntity;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Mostafaznv\Larupload\Enums\LaruploadNamingMethod;
use Mostafaznv\Larupload\Helpers\Slug;
use Mostafaznv\Larupload\Larupload;
use Mostafaznv\Larupload\UploadEntities;

trait UploadEntityName
{
    /**
     * Name of file
     */
    protected string $name;

    /**
     * Name of file in kebab case
     */
    protected string $nameKebab;

    /**
     * Specify the method that Larupload should use to name uploaded files.
     */
    protected LaruploadNamingMethod $namingMethod;

    /**
     * Language of file name
     */
    protected ?string $lang;


    public function getName(bool $withNameStyle = false): string
    {
        return $withNameStyle ? $this->nameStyle($this->name) : $this->name;
    }

    public function namingMethod(LaruploadNamingMethod $method): UploadEntities
    {
        $this->namingMethod = $method;

        return $this;
    }

    public function lang(string $lang): UploadEntities
    {
        $this->lang = $lang;

        return $this;
    }

    /**
     * Check whether we should convert the name to camel-case style.
     */
    protected function nameStyle($name): string
    {
        return $this->camelCaseResponse ? Str::camel($name) : $name;
    }
}
