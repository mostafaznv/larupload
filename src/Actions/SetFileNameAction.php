<?php

namespace Mostafaznv\Larupload\Actions;

use Illuminate\Http\UploadedFile;
use Mostafaznv\Larupload\Enums\LaruploadNamingMethod;
use Mostafaznv\Larupload\Helpers\Slug;

class SetFileNameAction
{
    public function __construct(
        private readonly UploadedFile          $file,
        private readonly LaruploadNamingMethod $namingMethod,
        private readonly ?string               $lang = null
    ) {}

    public static function make(UploadedFile $file, LaruploadNamingMethod $namingMethod, ?string $lang = null): static
    {
        return new static($file, $namingMethod, $lang);
    }


    public function generate(): string
    {
        $format = $this->file->getClientOriginalExtension();

        $name = match ($this->namingMethod) {
            LaruploadNamingMethod::HASH_FILE => $this->generateHashFile(),
            LaruploadNamingMethod::TIME      => time(),
            default                          => $this->generateSlugFromFile(),
        };

        return "$name.$format";
    }

    private function generateHashFile(): string
    {
        return hash_file('md5', $this->file->getRealPath());
    }

    private function generateSlugFromFile(): string
    {
        $name = $this->file->getClientOriginalName();
        $name = pathinfo($name, PATHINFO_FILENAME);
        $num = rand(0, 9999);

        $slug = Slug::make($this->lang)->generate($name);

        return "$slug-$num";
    }
}
