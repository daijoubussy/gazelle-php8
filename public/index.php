<?php

declare(strict_types=1);

/**
 * Gazelle Application Entry Point
 *
 * This is the single entry point for all HTTP requests.
 */

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Load Composer autoloader
require BASE_PATH . '/vendor/autoload.php';

// Load environment helper
require BASE_PATH . '/src/helpers.php';

use Gazelle\Core\Bootstrap;
use Gazelle\Core\Http\Request;

// Boot the application
$container = Bootstrap::boot(BASE_PATH);

// Capture the request
$request = Request::capture();

// Handle the request
$response = Bootstrap::handle($request);

// Send the response
$response->send();

// Terminate
Bootstrap::terminate();
