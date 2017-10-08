<?php
/**
 * Version information
 *
 * @package    pornbot
 * @copyright  2017 Joseph Felix
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace Pornbot\Core;

/**
 * Class Config
 * @package Pornbot\Core
 */
class Config
{
    /**
     * Retorna todas as configurações
     * @return array
     */
    public static function all()
    {
        global $config;

        if ($config === NULL) {
            require 'config.php';
        }

        return $config;
    }

    /**
     * Busca uma configuração do arquivo config.php
     * @param $index
     * @return mixed
     */
    public static function get($index)
    {
        global $config;

        if ($config === NULL) {
            require 'config.php';
        }

        return $config[$index];
    }

    /**
     * Altera uma configuração do arquivo config.php
     * @param $index
     * @param $value
     */
    public static function set($index, $value)
    {
        global $config;
        $config[$index] = $value;
    }
}