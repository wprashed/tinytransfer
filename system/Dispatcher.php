<?php
namespace system;

class Dispatcher
{
    protected $events = [];
    protected $filters = [];
    /**
     * Dispatches an event.
     */
    public function run($name, array $params = [])
    {
        $output = '';
        // Run pre-filters
        if (!empty($this->filters[$name]['before'])) {
            $this->filter($this->filters[$name]['before'], $params, $output);
        }
        // Run requested method
        $output = $this->execute($this->get($name), $params);
        // Run post-filters
        if (!empty($this->filters[$name]['after'])) {
            $this->filter($this->filters[$name]['after'], $params, $output);
        }
        return $output;
    }
    /**
     * Assigns a callback to an event.
     */
    public function set($name, $callback)
    {
        $this->events[$name] = $callback;
    }
    /**
     * Gets an assigned callback.
     */
    public function get($name)
    {
        return isset($this->events[$name]) ? $this->events[$name] : null;
    }
    /**
     * Checks if an event has been set.
     */
    public function has($name)
    {
        return isset($this->events[$name]);
    }
    /**
     * Clears an event. If no name is given,all events are removed.
     */
    public function clear($name = null)
    {
        if ($name !== null) {
            unset($this->events[$name]);
            unset($this->filters[$name]);
        } else {
            $this->events = [];
            $this->filters = [];
        }
    }
    /**
     * Hooks a callback to an event.
     */
    public function hook($name, $type, $callback)
    {
        $this->filters[$name][$type][] = $callback;
    }

    /**
     * Executes a chain of method filters.
     */
    public function filter($filters, &$params, &$output)
    {
        $args = array(&$params, &$output);
        foreach ($filters as $callback) {
            $continue = $this->execute($callback, $args);
            if ($continue === false) {
                break;
            }
        }
    }
    /**
     * Executes a callback function.
     */
    public static function execute($callback, array &$params = [])
    {
        if (is_callable($callback)) {
            return is_array($callback) ?
            self::invokeMethod($callback, $params) :
            self::callFunction($callback, $params);
        } elseif (stripos($callback, '@') !== false) {
            throw new \Exception('Invalid callback specified.');
        }
    }
    /**
     * Calls a function.
     */
    public static function callFunction($func, array &$params = [])
    {

        // Call static method
        if (is_string($func) && strpos($func, '::') !== false) {
            return call_user_func_array($func, $params);
        }

        switch (count($params)) {
            case 0:
                return $func();
            case 1:
                return $func($params[0]);
            case 2:
                return $func($params[0], $params[1]);
            case 3:
                return $func($params[0], $params[1], $params[2]);
            case 4:
                return $func($params[0], $params[1], $params[2], $params[3]);
            case 5:
                return $func($params[0], $params[1], $params[2], $params[3], $params[4]);
            default:
                return call_user_func_array($func, $params);
        }
    }
    /**
     * Invokes a method.
     */
    public static function invokeMethod($func, array &$params = [])
    {
        list($class, $method) = $func;
        $instance = is_object($class);
        switch (count($params)) {
            case 0:
                return ($instance) ?
                $class->$method() :
                $class::$method();
            case 1:
                return ($instance) ?
                $class->$method($params[0]) :
                $class::$method($params[0]);
            case 2:
                return ($instance) ?
                $class->$method($params[0], $params[1]) :
                $class::$method($params[0], $params[1]);
            case 3:
                return ($instance) ?
                $class->$method($params[0], $params[1], $params[2]) :
                $class::$method($params[0], $params[1], $params[2]);
            case 4:
                return ($instance) ?
                $class->$method($params[0], $params[1], $params[2], $params[3]) :
                $class::$method($params[0], $params[1], $params[2], $params[3]);
            case 5:
                return ($instance) ?
                $class->$method($params[0], $params[1], $params[2], $params[3], $params[4]) :
                $class::$method($params[0], $params[1], $params[2], $params[3], $params[4]);
            default:
                return call_user_func_array($func, $params);
        }
    }
    /**
     * Resets the object to the initial state.
     */
    public function reset()
    {
        $this->events = [];
        $this->filters = [];
    }
}
