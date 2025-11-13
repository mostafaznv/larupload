<?php

namespace Mostafaznv\Larupload\Concerns\Standalone;

use Mostafaznv\Larupload\Enums\LaruploadMode;
use Mostafaznv\Larupload\Larupload;

trait BootStandaloneLarupload
{
    protected bool $internalFunctionIsCallable = false;


    public function __construct(string $name, LaruploadMode $mode)
    {
        parent::__construct($name, LaruploadMode::STANDALONE);
    }


    public static function init(string $name): Larupload
    {
        $instance = new self($name, LaruploadMode::STANDALONE);
        $instance->id = time();

        return $instance;
    }
}
