<?php

namespace system\db\Json;

class JSONDB
{
    protected static $collections = [];

    protected static $macros = [];

    public static function open($file, array $options = array())
    {
        if (!isset(static::$collections[$file])) {
            static::$collections[$file] = new \system\db\Json\Collection($file, $options);
        }

        $collection = static::$collections[$file];

        // Register macros
        foreach (static::$macros as $name => $callback) {
            $collection->macro($name, $callback);
        }

        return $collection;
    }

    public static function macro($name, callable $callback)
    {
        static::$macros[$name] = $callback;
    }
}
