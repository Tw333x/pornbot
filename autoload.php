<?php
$directory = 'Sites';
if (is_readable($directory)) {
    $dir = dir($directory);
    while ($filename = $dir->read()) {
        if ($filename !== '.' && $filename !== '..') {
            require_once __DIR__ . DIRECTORY_SEPARATOR . $directory . DIRECTORY_SEPARATOR . $filename;
        }
    }
    $dir->close();
}