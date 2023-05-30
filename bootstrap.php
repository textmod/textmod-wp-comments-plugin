<?php


// Define the source and destination paths
use TextMod\Test\Helpers\CustomDbConnect;

$sourcePath = __DIR__ . '/../../../wp/wp-config.php';

// Set error reporting level to capture warnings
error_reporting(E_ALL);

// Restore error handler
restore_error_handler();

// Read the contents of the copied file
$configContents = file_get_contents($sourcePath);
if($configContents === false) {
    die('Failed to read wp-config.php file.');
}

$mysqlPort = getenv('WP_MYSQL_PORT');

// Replace the database host with 'localhost:51418'
$modifiedContents = str_replace("define( 'DB_HOST', 'mysql' );", "define('DB_HOST', '127.0.0.1:$mysqlPort');", $configContents);

// Save the modified contents back to the file
if (file_put_contents($sourcePath, $modifiedContents) === false) {
    die('Failed to save modified wp-config.php file.');
}

define('MYSQL_CLIENT_FLAGS', MYSQLI_CLIENT_SSL);

require_once(__DIR__ . '/vendor/autoload.php');
require_once(__DIR__ . '/../../../wp/src/wp-load.php');

$instance = new CustomDbConnect(DB_USER, DB_PASSWORD, DB_NAME, DB_HOST);
$GLOBALS['wpdb'] = $instance;
