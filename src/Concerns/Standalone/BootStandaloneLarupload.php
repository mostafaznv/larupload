<?php

namespace Mostafaznv\Larupload\Concerns\Standalone;

use Mostafaznv\Larupload\Enums\LaruploadMode;
use Mostafaznv\Larupload\Larupload;

trait BootStandaloneLarupload
{
    protected static string $laruploadNull;

    protected bool $internalFunctionIsCallable = false;


    public function __construct(string $name, LaruploadMode $mode)
    {
        static::$laruploadNull = crc32(time());

        if (!defined('LARUPLOAD_NULL')) {
            // @codeCoverageIgnoreStart
            define('LARUPLOAD_NULL', static::$laruploadNull);
            // @codeCoverageIgnoreEnd
        }

        parent::__construct($name, LaruploadMode::STANDALONE);
    }


    public static function init(string $name): Larupload
    {
        $instance = new self($name, LaruploadMode::STANDALONE);
        $instance->id = time();

        return $instance;
    }
}
