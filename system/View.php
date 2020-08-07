<?php
namespace system;

class View
{
    public $path;
    public $extension = '.php';
    protected $vars = [];
    private $template;

    public function __construct($path = '')
    {
        $this->path = $path;
    }
    /**
     * Gets a template variable.
     */
    public function get($key)
    {
        return isset($this->vars[$key]) ? $this->vars[$key] : null;
    }
    /**
     * Sets a template variable.
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
     * Checks if a template variable is set.
     */
    public function has($key)
    {
        return isset($this->vars[$key]);
    }
    /**
     * Unsets a template variable. If no key is passed in, clear all variables.
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
     * Renders a template.
     */
    public function render($file, $data = null)
    {
        $this->template = $this->getTemplate($file);

        if (!file_exists($this->template)) {
            throw new \Exception("Template file not found: {$this->template}.");
        }
        if (is_array($data)) {
            $this->vars = array_merge($this->vars, $data);
        }
        extract($this->vars);
        include $this->template;
    }
    /**
     * Gets the output of a template.
     */
    public function fetch($file, $data = null)
    {
        ob_start();
        $this->render($file, $data);
        $output = ob_get_clean();
        return $output;
    }
    /**
     * Checks if a template file exists.
     */
    public function exists($file)
    {
        return file_exists($this->getTemplate($file));
    }
    /**
     * Gets the full path to a template file.
     */
    public function getTemplate($file)
    {
        $ext = $this->extension;
        if (!empty($ext) && (substr($file, -1 * strlen($ext)) != $ext)) {
            $file .= $ext;
        }
        if ((substr($file, 0, 1) == '/')) {
            return $file;
        }
        if (empty($this->path)) {
            return $file;
        } else {
            return $this->path . '/' . $file;
        }
    }
    /**
     * Displays escaped output.
     */
    public function e($str, $val="")
    {
        echo empty($str) ? $val : htmlentities($str);
    }
    /**
     * Displays html output.
     */
    public function h($str, $val="")
    {
        echo empty($str) ? $val : htmlspecialchars_decode($str);
    }
}
