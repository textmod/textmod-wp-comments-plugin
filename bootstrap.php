<?php

use TextMod\Test\Helpers\CustomDbConnect;

function rollbackContent()
{
    // Define the source and destination paths
    $sourcePath = getenv("WP_PATH") . '/wp-config.php';

    // Set error reporting level to capture warnings
    error_reporting(E_ALL);

    // Restore error handler
    restore_error_handler();

    // Revert the modified contents back to the original
    $originalContents = file_get_contents($sourcePath . '.bak');
    if ($originalContents !== false) {
        file_put_contents($sourcePath, $originalContents);
    }

    // Delete the backup file
    if (file_exists($sourcePath . '.bak')) {
        unlink($sourcePath . '.bak');
    }
}

function bootstrap()
{
    // Define the source and destination paths
    $sourcePath = getenv("WP_PATH") . '/wp-config.php';

    // Backup the original contents
    copy($sourcePath, $sourcePath . '.bak');

    // Read the contents of the copied file
    $configContents = file_get_contents($sourcePath);
    if ($configContents === false) {
        die('Failed to read wp-config.php file.');
    }

    exec("docker ps --format '{{.Names}}' | grep -E '\\\\bmysql\\\\b'", $output);
    $containerName = trim($output[0]);

    // Run the Docker shell command and capture the output
    $output = exec("docker port $containerName 3306 | grep -oE '[0-9]+$'");

    // Assign the result to a variable
    $mysqlPort = trim($output);

    if (empty($mysqlPort)) {
        throw new Exception('Failed to retrieve MySQL port.');
    }

    // Replace the database host with 'localhost:51418'
    $modifiedContents = str_replace("define( 'DB_HOST', 'mysql' );", "define('DB_HOST', '127.0.0.1:$mysqlPort');", $configContents);

    // Save the modified contents back to the file
    if (file_put_contents($sourcePath, $modifiedContents) === false) {
        die('Failed to save modified wp-config.php file.');
    }

    define('MYSQL_CLIENT_FLAGS', MYSQLI_CLIENT_SSL);

    require_once(__DIR__ . '/vendor/autoload.php');
    require_once(getenv("WP_PATH") . '/src/wp-load.php');

    $instance = new CustomDbConnect(DB_USER, DB_PASSWORD, DB_NAME, DB_HOST);
    $GLOBALS['wpdb'] = $instance;
}

function shutdown()
{
    // Call the rollback function
    rollbackContent();

    // Additional cleanup code or actions can be performed here
    // before PHP finishes execution.
}

// Register the shutdown function
register_shutdown_function('shutdown');

try {
    // Call the bootstrap function
    bootstrap();

    // The rest of your code...
} catch (Exception $e) {
    echo 'An error occurred: ' . $e->getMessage();
}
