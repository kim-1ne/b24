<?php

namespace B24\Devtools\Trait;

trait SingletonTrait
{
    private static $instance;

    protected function __construct() {}

    public static function getInstance(): static
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }
}
