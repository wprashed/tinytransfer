<?php
namespace system;

class UI
{
    private static $engine;
    /**
     * Handles calls to static methods.
     */
    public static function __callStatic($name, $params)
    {
        $app = UI::app();
        return \system\Dispatcher::invokeMethod(array($app, $name), $params);
    }
    /**
     * return Application instance
     */
    public static function app()
    {
        static $initialized = false;
        if (!$initialized) {
            // Loader autoload
            \system\Loader::autoload(true, dirname(__DIR__));
            // Engine
            self::$engine = new \system\Engine();
            $initialized = true;
        }
        return self::$engine;
    }
}
