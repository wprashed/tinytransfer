<?php
namespace system\http;

/* Route Class */
class Route
{
    public $pattern;
    public $callback;
    public $methods = [];
    public $params = [];
    public $regex;
    public $splat = '';
    public $pass = false;

    public function __construct($pattern, $callback, $methods, $pass)
    {
        $this->pattern = $pattern;
        $this->callback = $callback;
        $this->methods = $methods;
        $this->pass = $pass;
    }

    /**
     * Checks if a URL matches the route pattern. Also parses named parameters in the URL.
     */
    public function matchUrl($url, $case_sensitive = false)
    {
        // Wildcard or exact match
        if ($this->pattern === '*' || $this->pattern === $url) {
            return true;
        }
        $ids = [];
        $last_char = substr($this->pattern, -1);
        // Get splat
        if ($last_char === '*') {
            $n = 0;
            $len = strlen($url);
            $count = substr_count($this->pattern, '/');
            for ($i = 0; $i < $len; $i++) {
                if ($url[$i] == '/') {
                    $n++;
                }

                if ($n == $count) {
                    break;
                }
            }
            $this->splat = (string) substr($url, $i + 1);
        }
        // Build the regex for matching
        $regex = str_replace(array(')', '/*'), array(')?', '(/?|/.*?)'), $this->pattern);
        $regex = preg_replace_callback(
            '#@([\w]+)(:([^/\(\)]*))?#',
            function ($matches) use (&$ids) {
                $ids[$matches[1]] = null;
                if (isset($matches[3])) {
                    return '(?P<' . $matches[1] . '>' . $matches[3] . ')';
                }
                return '(?P<' . $matches[1] . '>[^/\?]+)';
            },
            $regex
        );
        // Fix trailing slash
        if ($last_char === '/') {
            $regex .= '?';
        } else {
            $regex .= '/?';
        }
        // Attempt to match route and named parameters
        if (preg_match('#^' . $regex . '(?:\?.*)?$#' . (($case_sensitive) ? '' : 'i'), $url, $matches)) {
            foreach ($ids as $k => $v) {
                $this->params[$k] = (array_key_exists($k, $matches)) ? urldecode($matches[$k]) : null;
            }
            $this->regex = $regex;
            return true;
        }
        return false;
    }
    /**
     * Checks if an HTTP method matches the route methods.
     */
    public function matchMethod($method)
    {
        return count(array_intersect(array($method, '*'), $this->methods)) > 0;
    }
}

/* Router Class */
class Router
{
    protected $routes = [];
    protected $index = 0;
    public $case_sensitive = false;
    public function getRoutes()
    {
        return $this->routes;
    }
    public function clear()
    {
        $this->routes = [];
    }
    /**
     * Maps a URL pattern to a callback function.
     */
    public function map($pattern, $callback, $pass_route = false)
    {
        $url = $pattern;
        $methods = array('*');
        if (strpos($pattern, ' ') !== false) {
            list($method, $url) = explode(' ', trim($pattern), 2);
            $url = trim($url);
            $methods = explode('|', $method);
        }
        $this->routes[] = new Route($url, $callback, $methods, $pass_route);
    }

    /**
     * Routes the current request.
     */
    public function route(Request $request)
    {
        $url_decoded = urldecode($request->url);
        while ($route = $this->current()) {
            if ($route !== false && $route->matchMethod($request->method) && $route->matchUrl($url_decoded, $this->case_sensitive)) {
                return $route;
            }
            $this->next();
        }
        return false;
    }
    /**
     * Gets the current route.
     */
    public function current()
    {
        return isset($this->routes[$this->index]) ? $this->routes[$this->index] : false;
    }
    /**
     * Gets the next route.
     */
    public function next()
    {
        $this->index++;
    }
    /**
     * Reset to the first route.
     */
    public function reset()
    {
        $this->index = 0;
    }
}
