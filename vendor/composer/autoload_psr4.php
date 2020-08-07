<?php

// autoload_psr4.php @generated by Composer

$vendorDir = dirname(dirname(__FILE__));
$baseDir = dirname($vendorDir);

return array(
    'system\\' => array($baseDir . '/system'),
    'module\\tinyTransfer\\' => array($baseDir . '/module/mod_tiny_transfer/controller', $baseDir . '/module/mod_tiny_transfer/library'),
    'module\\statistics\\' => array($baseDir . '/module/mod_statistics/controller'),
    'module\\smtp\\' => array($baseDir . '/module/mod_smtp/controller'),
    'admin\\' => array($baseDir . '/admin/controller'),
    'PHPMailer\\PHPMailer\\' => array($vendorDir . '/phpmailer/phpmailer/src'),
);