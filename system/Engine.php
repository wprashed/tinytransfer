<?php
namespace system;

use system\Dispatcher;
use system\helpers\Pagination;
use system\Loader;

class Engine
{
    protected $vars;
    protected $loader;
    protected $dispatcher;
    public function __construct()
    {
        $this->vars = [];
        $this->loader = new Loader();
        $this->dispatcher = new Dispatcher();
        $this->init();
    }

    /**
     * Handles calls to class methods.
     */
    public function __call($name, $params)
    {
        $callback = $this->dispatcher->get($name);

        if (is_callable($callback)) {
            return $this->dispatcher->run($name, $params);
        }
        if (!$this->loader->get($name)) {
            throw new \Exception("{$name} must be a mapped method.");
        }
        $shared = (!empty($params)) ? (bool) $params[0] : true;
        return $this->loader->load($name, $shared);
    }

    /**
     * Initializes the framework.
     */
    public function init()
    {
        static $initialized = false;
        $self = $this;

        if ($initialized) {
            $this->vars = [];
            $this->loader->reset();
            $this->dispatcher->reset();
        }

        // Register default components
        $this->loader->register('request', '\system\http\Request');
        $this->loader->register('response', '\system\http\Response');
        $this->loader->register('router', '\system\http\Router');
        $this->loader->register('fs', '\system\helpers\Fs');
        $this->loader->register('safe', '\system\helpers\Safe');
        $this->loader->register('color', '\system\helpers\Color');
        $this->loader->register('view', '\system\View', [], function ($view) use ($self) {
            $view->extension = $self->get('ui.views.extension');
        });

        $this->map('pagination', function ($target = [], $defaultPageSize = 8, $options = []) {
            return new Pagination($target, $defaultPageSize, $options);
        });

        // Register framework methods
        $methods = array(
            'start', 'stop', 'route', 'halt', 'error', 'notFound',
            'render', 'fetch', 'redirect', 'lastModified', 'json', 'runHook',
        );
        foreach ($methods as $name) {
            $this->dispatcher->set($name, array($this, '_' . $name));
        }
        // Default configuration settings
        $this->set('ui.base_url', null);
        $this->set('ui.case_sensitive', false);
        $this->set('ui.handle_errors', true);
        $this->set('ui.log_errors', false);
        $this->set('ui.views.extension', '.php');
        // Startup configuration
        $this->before('start', function () use ($self) {
            // Enable error handling
            if ($self->get('ui.handle_errors')) {
                set_error_handler(array($self, 'handleError'));
                set_exception_handler(array($self, 'handleException'));
            }
            // Set case-sensitivity
            $self->router()->case_sensitive = $self->get('ui.case_sensitive');
        });

        $initialized = true;
    }
    /**
     * Custom error handler. Converts errors into exceptions.
     */
    public function handleError($errno, $errstr, $errfile, $errline)
    {
        if ($errno & error_reporting()) {
            throw new \ErrorException($errstr, $errno, 0, $errfile, $errline);
        }
    }
    /**
     * Custom exception handler. Logs exceptions.
     */
    public function handleException($e)
    {
        if ($this->get('ui.log_errors')) {
            error_log($e->getMessage());
        }
        $this->error($e);
    }
    /**
     * Maps a callback to a framework method.
     */
    public function map($name, $callback)
    {
        if (method_exists($this, $name)) {
            throw new \Exception('Cannot override an existing framework method.');
        }
        $this->dispatcher->set($name, $callback);
    }
    /**
     * Registers a class to a framework method.
     */
    public function register($name, $class, array $params = [], $callback = null)
    {
        if (method_exists($this, $name)) {
            throw new \Exception('Cannot override an existing framework method.');
        }

        $this->loader->register($name, $class, $params, $callback);
    }
    /**
     * Adds a pre-filter to a method.
     */
    public function before($name, $callback)
    {
        $this->dispatcher->hook($name, 'before', $callback);
    }
    /**
     * Adds a post-filter to a method.
     */
    public function after($name, $callback)
    {
        $this->dispatcher->hook($name, 'after', $callback);
    }
    /**
     * Gets a variable.
     */
    public function get($key = null)
    {
        if ($key === null) {
            return $this->vars;
        }

        return isset($this->vars[$key]) ? $this->vars[$key] : null;
    }
    /**
     * Sets a variable.
     */
    public function set($key, $value = null)
    {
        if (is_array($key) || is_object($key)) {
            foreach ($key as $k => $v) {
                $this->vars[$k] = $v;
            }
        } else {
            $this->vars[$key] = $value;
        }
    }
    /**
     * Checks if a variable has been set.
     */
    public function has($key)
    {
        return isset($this->vars[$key]);
    }
    /**
     * Unsets a variable. If no key is passed in, clear all variables.
     */
    public function clear($key = null)
    {
        if (is_null($key)) {
            $this->vars = [];
        } else {
            unset($this->vars[$key]);
        }
    }
    /**
     * Adds a path for class autoloading.
     */
    public function path($dir)
    {
        $this->loader->addDirectory($dir);
    }
    /**
     * Starts the framework.
     */
    public function _start()
    {
        $dispatched = false;
        $self = $this;
        $request = $this->request();
        $response = $this->response();
        $router = $this->router();
        // Allow filters to run
        $this->after('start', function () use ($self) {
            $self->stop();
        });
        // Flush any existing output
        if (ob_get_length() > 0) {
            $response->write(ob_get_clean());
        }
        // Enable output buffering
        ob_start();

        // Route the request
        while ($route = $router->route($request)) {
            $params = array_values($route->params);
            // Add route info to the parameter list
            if ($route->pass) {
                $params[] = $route;
            }
            // Call route handler
            $continue = $this->dispatcher->execute(
                $route->callback,
                $params
            );
            
            $dispatched = true;
            if (!$continue) {
                break;
            }

            $router->next();
            $dispatched = false;
        }
        if (!$dispatched) {
            $this->notFound();
        }
    }
    /**
     * Stops the framework and outputs the current response.
     */
    public function _stop($code = null)
    {
        $response = $this->response();
        if (!$response->sent()) {
            if ($code !== null) {
                $response->status($code);
            }
            $response->write(ob_get_clean());
            $response->send();
        }
    }
    /**
     * Routes a URL to a callback function.
     */
    public function _route($pattern, $callback, $pass_route = false)
    {
        $this->router()->map($pattern, $callback, $pass_route);
    }
    /**
     * Stops processing and returns a given response.
     */
    public function _halt($code = 200, $message = '')
    {
        $this->response()
            ->clear()
            ->status($code)
            ->write($message)
            ->send();
        exit();
    }
    /**
     * Sends an HTTP 500 response for any errors.
     */
    public function _error($e)
    {
        $msg = sprintf(
            '<h1>500 Internal Server Error</h1>' .
            '<h3>%s (%s)</h3>' .
            '<pre>%s</pre>',
            $e->getMessage(),
            $e->getCode(),
            $e->getTraceAsString()
        );

        try {
            $this->response()
                ->clear()
                ->status(500)
                ->write($msg)
                ->send();
        } catch (\Throwable $t) { // PHP 7.0+
            exit($msg);
        } catch (\Exception $e) { // PHP < 7
            exit($msg);
        }
    }
    /**
     * Sends an HTTP 404 response when a URL is not found.
     */
    public function _notFound()
    {
        $this->response()
            ->clear()
            ->status(404)
            ->write('<h1>404 Not Found</h1>' . '<h3>The page you have requested could not be found.</h3>' . str_repeat(' ', 512))
            ->send();
    }
    /**
     * Redirects the current request to another URL.
     */
    public function _redirect($url, $code = 303)
    {
        $base = $this->get('ui.base_url');
        if ($base === null) {
            $base = $this->request()->base;
        }
        // Append base url to redirect url
        if ($base != '/' && strpos($url, '://') === false) {
            $url = $base . preg_replace('#/+#', '/', '/' . $url);
        }
        $this->response()
            ->clear()
            ->status($code)
            ->header('Location', $url)
            ->send();
    }
    /**
     * Renders a template.
     */
    public function _render($file, $data = null, $key = null)
    {
        if ($key !== null) {
            $this->view()->set($key, $this->view()->fetch($file, $data));
        } else {
            $this->view()->render($file, $data);
        }
    }

    /**
     * Fetch a template.
     */
    public function _fetch($file, $data = null)
    {
        return $this->view()->fetch($file, $data);
    }

    /**
     * Sends a JSON response.
     */
    public function _json(
        $data,
        $code = 200,
        $encode = true,
        $charset = 'utf-8',
        $option = 0
    ) {
        $json = ($encode) ? json_encode($data, $option) : $data;

        $this->response()
            ->status($code)
            ->header('Content-Type', 'application/json; charset=' . $charset)
            ->write($json)
            ->send();
    }
    /**
     * Handles last modified HTTP caching.
     */
    public function _lastModified($time)
    {
        $this->response()->header('Last-Modified', gmdate('D, d M Y H:i:s \G\M\T', $time));

        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) &&
            strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) === $time) {
            $this->halt(304);
        }
    }

    /**
     * Run Hook
     */
    public function _runHook($name = "")
    {
        return $this->dispatcher->run($name);
    }
}
