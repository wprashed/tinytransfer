<?php
define('VERSION', '1.1.5.20200721');
define('DEBUG', false);
define('START_TIME', microtime(true));
define('START_MEM', memory_get_usage());
define('DS', DIRECTORY_SEPARATOR);
define('ROOT_PATH', __DIR__ . DS);
define('SYSTEM_PATH', ROOT_PATH . 'system' . DS);
define('ADMIN_PATH', ROOT_PATH . 'admin' . DS);
define('MODULE_PATH', ROOT_PATH . 'module' . DS);
define('TMP_PATH', ROOT_PATH . 'tmp' . DS);
define('VENDOR_PATH', ROOT_PATH . 'vendor' . DS);

// Composer autoload
if (is_file(VENDOR_PATH . 'autoload.php')) {
    require_once VENDOR_PATH . 'autoload.php';
} else {
    exit("no autoload.php");
}

// Require Admin Routes
require ADMIN_PATH . 'routes.php';

\system\UI::before('start', [\system\Bootstrap::class, 'start']);
\system\UI::after('start', [\system\Bootstrap::class, 'end']);
\system\UI::start();
