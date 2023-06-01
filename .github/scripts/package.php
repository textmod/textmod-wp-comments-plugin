<?php

// Define the plugin directory path
$pluginDir = __DIR__ . '/../../';

// Define the included files and directories
$includedFiles = [
    'readme.txt',
    'license.txt',
    'textmod-wp-comments-plugin.php',
];

$includedDirs = [
    'src',
    'vendor',
];

// Get the version number from the environment variable
$version = getenv('PLUGIN_VERSION');

// Define the output directory
$distDir = __DIR__ . '/../../dist';

// Create the temporary directory inside the dist folder
$tempDir = $distDir . '/textmod-wp-comments-plugin';
if (!file_exists($tempDir)) {
    mkdir($tempDir, 0755, true);
}

// Copy the included files and directories to the temporary directory
copyIncludedFiles($includedFiles, $pluginDir, $tempDir);
copyIncludedDirs($includedDirs, $pluginDir, $tempDir);

// Create the zip file
$zipFileName = "textmod-wp-comments-plugin-v{$version}.zip";
$zipFilePath = $distDir . '/' . $zipFileName;
createZip($tempDir, $zipFilePath);

// Clean up the temporary directory
rrmdir($tempDir);

echo "Plugin zip file created successfully!" . PHP_EOL;

/**
 * Copy the included files to the destination directory.
 *
 * @param array $files Included files
 * @param string $src Source directory
 * @param string $dst Destination directory
 */
function copyIncludedFiles($files, $src, $dst) {
    $stack = [[$files, $src, $dst]];

    while (!empty($stack)) {
        $pop = array_pop($stack);
        $currentFiles = $pop[0];
        $currentSrc = $pop[1];
        $currentDst = $pop[2];

        foreach ($currentFiles as $file) {
            $srcFile = $currentSrc . '/' . $file;
            $dstFile = $currentDst . '/' . $file;
            if (file_exists($srcFile)) {
                if (is_dir($srcFile)) {
                    if (!file_exists($dstFile)) { // Check if the destination directory already exists
                        mkdir($dstFile, 0755, true);
                    }

                    $nextFiles = array_diff(scandir($srcFile), ['.', '..']);
                    $nextSrc = $srcFile;
                    $nextDst = $dstFile;
                    $stack[] = [$nextFiles, $nextSrc, $nextDst];
                } else {
                    copy($srcFile, $dstFile);
                }
            }
        }
    }
}

/**
 * Copy the included directories to the destination directory.
 *
 * @param array $dirs Included directories
 * @param string $src Source directory
 * @param string $dst Destination directory
 */
function copyIncludedDirs($dirs, $src, $dst) {
    foreach ($dirs as $dir) {
        $srcDir = $src . '/' . $dir;
        $dstDir = $dst . '/' . $dir;
        if (file_exists($srcDir) && is_dir($srcDir)) {
            if (!file_exists($dstDir)) { // Check if the destination directory already exists
                mkdir($dstDir, 0755, true);
            }

            $includedFiles = array_diff(scandir($srcDir), ['.', '..']);
            copyIncludedFiles($includedFiles, $srcDir, $dstDir);
            copyIncludedDirs($includedFiles, $srcDir, $dstDir);
        }
    }
}

/**
 * Recursively remove directory and its contents.
 *
 * @param string $dir Directory path
 */
function rrmdir($dir) {
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != '.' && $object != '..') {
                if (is_dir($dir . '/' . $object)) {
                    rrmdir($dir . '/' . $object);
                } else {
                    unlink($dir . '/' . $object);
                }
            }
        }
        rmdir($dir);
    }
}

/**
 * Create a zip file from a directory.
 *
 * @param string $source Source directory
 * @param string $destination Destination zip file
 */
function createZip($source, $destination) {
    $zip = new ZipArchive();
    if ($zip->open($destination, ZipArchive::CREATE) === true) {
        $source = realpath($source);
        if (is_dir($source)) {
            $iterator = new RecursiveDirectoryIterator($source);
            $iterator->setFlags(RecursiveDirectoryIterator::SKIP_DOTS);
            $files = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST);
            foreach ($files as $file) {
                $file = realpath($file);
                if (is_dir($file)) {
                    $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
                } else {
                    $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
                }
            }
        } else {
            $zip->addFromString(basename($source), file_get_contents($source));
        }
        $zip->close();
    }
}
