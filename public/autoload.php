<?php

if (version_compare('5.6.30', PHP_VERSION, '>')) {
    fwrite(
        STDERR,
        sprintf(
            'This version of Migrate is supported on PHP 5.6.30.' . PHP_EOL .
            'You are using PHP %s (%s).' . PHP_EOL,
            PHP_VERSION,
            PHP_BINARY
        )
    );
    die(1);
}
if (!ini_get('date.timezone')) {
    ini_set('date.timezone', 'UTC');
}
foreach (array(__DIR__ . '/../../../autoload.php', __DIR__ . '/../vendor/autoload.php', __DIR__ . '/vendor/autoload.php') as $file) {
    if (file_exists($file)) {
        define('COMPOSER_INSTALL', $file);
        break;
    }
}
unset($file);
if (!defined('COMPOSER_INSTALL')) {
    echo 'Please run composer install';
    die(1);
}
$options = getopt('', array('prepend:'));
if (isset($options['prepend'])) {
    require $options['prepend'];
}
unset($options);
require COMPOSER_INSTALL;

error_reporting(E_ALL);
ini_set('display_errors', true);
