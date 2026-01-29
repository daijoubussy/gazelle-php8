<?php

declare(strict_types=1);

/**
 * PHPUnit Bootstrap File
 *
 * Sets up the testing environment for Gazelle
 */

// Define test constants
define('TESTING', true);
define('SERVER_ROOT', dirname(__DIR__));

// Required for tests to run without a full environment
if (!defined('SITE_NAME')) {
    define('SITE_NAME', 'TestGazelle');
}
if (!defined('SITE_DOMAIN')) {
    define('SITE_DOMAIN', 'test.example.com');
}
if (!defined('STATIC_SERVER')) {
    define('STATIC_SERVER', '/static/');
}
if (!defined('ENCKEY')) {
    define('ENCKEY', 'test_encryption_key_for_testing');
}
if (!defined('IMAGE_PSK')) {
    define('IMAGE_PSK', 'test_image_psk_for_testing_only');
}

// Autoload via composer
require_once SERVER_ROOT . '/vendor/autoload.php';
