<?php

namespace Mostafaznv\Larupload\Concerns\Standalone;

use Mostafaznv\Larupload\Larupload;
use Mostafaznv\Larupload\LaruploadEnum;

trait BootStandaloneLarupload
{
    protected static string $laruploadNull;

    protected bool $internalFunctionIsCallable = false;


    public function __construct(string $name, string $mode)
    {
        static::$laruploadNull = crc32(time());

        if (!defined('LARUPLOAD_NULL')) {
            define('LARUPLOAD_NULL', static::$laruploadNull);
        }

        parent::__construct($name, LaruploadEnum::STANDALONE_MODE);
    }


    public static function init(string $name): Larupload
    {
        $instance = new self($name, LaruploadEnum::STANDALONE_MODE);
        $instance->id = time();

        return $instance;
    }
}
