<?php
namespace system\http;

/**
 * The Collection class allows you to access a set of data
 */
class Collection implements \ArrayAccess, \Iterator, \Countable
{
    private $data;
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }
    /**
     * Gets an item.
     */
    public function __get($key)
    {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }
    /**
     * Set an item.
     */
    public function __set($key, $value)
    {
        $this->data[$key] = $value;
    }
    /**
     * Checks if an item exists.
     */
    public function __isset($key)
    {
        return isset($this->data[$key]);
    }
    /**
     * Removes an item.
     */
    public function __unset($key)
    {
        unset($this->data[$key]);
    }
    /**
     * Gets an item at the offset.
     */
    public function offsetGet($offset)
    {
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }
    /**
     * Sets an item at the offset.
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }
    /**
     * Checks if an item exists at the offset.
     */
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }
    /**
     * Removes an item at the offset.
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    /**
     * Resets the collection.
     */
    public function rewind()
    {
        reset($this->data);
    }
    /**
     * Gets current collection item.
     */
    public function current()
    {
        return current($this->data);
    }
    /**
     * Gets current collection key.
     */
    public function key()
    {
        return key($this->data);
    }

    /**
     * Gets the next collection value.
     */
    public function next()
    {
        return next($this->data);
    }

    /**
     * Checks if the current collection key is valid.
     */
    public function valid()
    {
        $key = key($this->data);
        return ($key !== null && $key !== false);
    }
    /**
     * Gets the size of the collection.
     */
    public function count()
    {
        return sizeof($this->data);
    }
    /**
     * Gets the item keys.
     */
    public function keys()
    {
        return array_keys($this->data);
    }
    /**
     * Gets the collection data.
     */
    public function getData()
    {
        return $this->data;
    }
    /**
     * Sets the collection data.
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }
    /**
     * Removes all items from the collection.
     */
    public function clear()
    {
        $this->data = [];
    }
}


/**
 * The Request class
 */
class Request
{
    public $url;
    public $base;
    public $method;
    public $referrer;
    public $ip;
    public $ajax;
    public $scheme;
    public $user_agent;
    public $type;
    public $length;
    public $query;
    public $data;
    public $cookies;
    public $files;
    public $secure;
    public $accept;
    public $proxy_ip;
    public $host;
    public $pjax;

    public function __construct($config = [])
    {
        // Default properties
        if (empty($config)) {
            $config = array(
                'url' => str_replace('@', '%40', self::getVar('REQUEST_URI', '/')),
                'base' => str_replace(array('\\', ' '), array('/', '%20'), dirname(self::getVar('SCRIPT_NAME'))),
                'method' => self::getMethod(),
                'referrer' => self::getVar('HTTP_REFERER'),
                'ip' => self::getVar('REMOTE_ADDR'),
                'ajax' => self::getVar('HTTP_X_REQUESTED_WITH') == 'XMLHttpRequest',
                'scheme' => self::getScheme(),
                'user_agent' => self::getVar('HTTP_USER_AGENT'),
                'type' => self::getVar('CONTENT_TYPE'),
                'length' => self::getVar('CONTENT_LENGTH', 0),
                'query' => new Collection($_GET),
                'data' => new Collection($_POST),
                'cookies' => new Collection($_COOKIE),
                'files' => new Collection($_FILES),
                'secure' => self::getScheme() == 'https',
                'accept' => self::getVar('HTTP_ACCEPT'),
                'proxy_ip' => self::getProxyIpAddress(),
                'host' => self::getVar('HTTP_HOST'),
                'pjax' => self::getVar('HTTP_X_PJAX'),
            );
        }
        $this->init($config);
    }

    /**
     * Initialize request properties.
     */
    public function init($properties = [])
    {
        // Set all the defined properties
        foreach ($properties as $name => $value) {
            $this->$name = $value;
        }
        // Get the requested URL without the base directory
        if ($this->base != '/' && strlen($this->base) > 0 && strpos($this->url, $this->base) === 0) {
            $this->url = substr($this->url, strlen($this->base));
        }
        // Default url
        if (empty($this->url)) {
            $this->url = '/';
        }
        // Merge URL query parameters with $_GET
        else {
            $_GET += self::parseQuery($this->url);
            $this->query->setData($_GET);
        }
        
        // Check for JSON input
        if (strpos($this->type, 'application/json') === 0) {
            $body = $this->getBody();
            if ($body != '') {
                $data = json_decode($body, true);
                if ($data != null) {
                    $this->data->setData($data);
                }
            }
        }
    }

    /**
     * Gets the body of the request.
     */
    public static function getBody()
    {
        static $body;
        if (!is_null($body)) {
            return $body;
        }
        $method = self::getMethod();
        if ($method == 'POST' || $method == 'PUT' || $method == 'DELETE' || $method == 'PATCH') {
            $body = file_get_contents('php://input');
        }
        return $body;
    }

    /**
     * Gets the request method.
     */
    public static function getMethod()
    {
        $method = self::getVar('REQUEST_METHOD', 'GET');
        if (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
            $method = $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];
        } elseif (isset($_REQUEST['_method'])) {
            $method = $_REQUEST['_method'];
        }
        return strtoupper($method);
    }
    /**
     * Gets the real remote IP address.
     */
    public static function getProxyIpAddress()
    {
        static $forwarded = array(
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
        );
        $flags = \FILTER_FLAG_NO_PRIV_RANGE | \FILTER_FLAG_NO_RES_RANGE;
        foreach ($forwarded as $key) {
            if (array_key_exists($key, $_SERVER)) {
                sscanf($_SERVER[$key], '%[^,]', $ip);
                if (filter_var($ip, \FILTER_VALIDATE_IP, $flags) !== false) {
                    return $ip;
                }
            }
        }
        return '';
    }

    /**
     * Gets a variable from $_SERVER using $default if not provided.
     */
    public static function getVar($var, $default = '')
    {
        return isset($_SERVER[$var]) ? $_SERVER[$var] : $default;
    }
    /**
     * Parse query parameters from a URL.
     */
    public static function parseQuery($url)
    {
        $params = [];
        $args = parse_url($url);
        if (isset($args['query'])) {
            parse_str($args['query'], $params);
        }
        return $params;
    }
    public static function getScheme()
    {
        if (
            (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) === 'on')
            ||
            (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
            ||
            (isset($_SERVER['HTTP_FRONT_END_HTTPS']) && $_SERVER['HTTP_FRONT_END_HTTPS'] === 'on')
            ||
            (isset($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] === 'https')
        ) {
            return 'https';
        }
        return 'http';
    }
}
