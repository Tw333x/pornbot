#!/usr/bin/php
<?php
/**
 * Version information
 *
 * @package    pornbot
 * @copyright  2017 Joseph Felix
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

spl_autoload_register(function ($className) {
    $filename = __DIR__ . DIRECTORY_SEPARATOR . str_replace('Pornbot\\', '', $className) . '.php';
    if (file_exists($filename)) {
        include_once $filename;
    }
});

try {
    $bootstrap = \Pornbot\Bootstrap::create(['run' => PHP_SAPI]);
    $bootstrap->init();
} catch (\RuntimeException $e) {
    echo $e->getMessage();
}