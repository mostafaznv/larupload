<?php

namespace Mostafaznv\Larupload\Actions;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Mostafaznv\Larupload\Enums\LaruploadNamingMethod;
use Mostafaznv\Larupload\Helpers\Slug;


readonly class SetFileNameAction
{
    public function __construct(
        private UploadedFile          $file,
        private LaruploadNamingMethod $namingMethod,
        private ?string               $lang = null
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
            LaruploadNamingMethod::TIME      => Carbon::now()->unix(),
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
