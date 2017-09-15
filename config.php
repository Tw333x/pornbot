<?php
/**
 * Version information
 *
 * @package    pornbot
 * @copyright  2017 Joseph Felix
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$config = [];

$config['db_type'] = 'mysql';
$config['db_host'] = '127.0.0.1';
$config['db_user'] = 'root';
$config['db_pass'] = '';
$config['db_schema'] = 'analnymous';
$config['export_to'] = 'wordpress';
$config['dirroot'] = 'C:/wamp/www/pornbot';
$config['libdir'] = $config['dirroot'] . DIRECTORY_SEPARATOR . 'lib';
$config['debug'] = true;
$config['max_videos'] = 10;
$config['timezone'] = 'America/Sao_Paulo';
$config['timeout'] = 0;