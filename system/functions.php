<?php

/**
 * object to array
 *
 * @param object $obj
 * @return array
 */
function object_to_array($obj)
{
    $obj = (array)$obj;
    foreach ($obj as $k => $v) {
        if (gettype($v) == 'resource') {
            return;
        }
        if (gettype($v) == 'object' || gettype($v) == 'array') {
            $obj[$k] = (array)object_to_array($v);
        }
    }
 
    return $obj;
}

// Get plugin page hookname
function get_plugin_page_hookname($plugin_page)
{
    global $admin_page_hooks;

    $page_type = 'admin';
    if (isset($admin_page_hooks[$plugin_page])) {
        if (isset($admin_page_hooks[$plugin_page])) {
            $page_type = 'toplevel';
        }
    }

    $plugin_name = preg_replace('!\.php!', '', $plugin_page);

    return $page_type . '_page_' . $plugin_name;
}

// Add menu page
function add_menu_page($page_title, $menu_title, $menu_slug, $function = '', $icon_class = '', $show=true)
{
    global $admin_menu;
    $hookname = get_plugin_page_hookname($menu_slug, '');
    if (!empty($function) && !empty($hookname)) {
        \system\UI::map($hookname, $function);
    }

    $admin_menu[$menu_slug] = [$menu_title, $menu_slug, $page_title, 'menu-top ' . $icon_class, $hookname, $show];
    return $hookname;
}

// Add widgets
function add_widgets($name, $function = '')
{
    global $admin_widgets;
    $hookname = 'widgets_'.$name;
    if (!empty($function)) {
        \system\UI::map($hookname, $function);
    }
    $admin_widgets[$name] = $hookname;
}

// session
function __start_session()
{
    if (!defined('SESSION_START')) {
        define('SESSION_START', true);
        session_set_cookie_params(86400 * 30);
        session_start();
    }
}

function set_session($key, $value, $maxage = 86400)
{
    __start_session();
    $_SESSION[$key] = $value;
    $_SESSION[$key . "_end_time"] = time() + $maxage;
}
function get_session($key)
{
    __start_session();
    if (isset($_SESSION[$key . "_end_time"]) && $_SESSION[$key . "_end_time"] >= time()) {
        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        } else {
            return null;
        }
    } else {
        del_session($key);
        del_session($key . "_end_time");
        return null;
    }
}
function del_session($key)
{
    __start_session();
    unset($_SESSION[$key]);
}
