<?php
/**
 * Version information
 *
 * @package    pornbot
 * @copyright  2017 Joseph Felix
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace Pornbot\Export;

/**
 * Class ExportBase
 * @package Pornbot\Export
 */
abstract class ExportBase
{
    /**
     * Inicia o processo de registro dos vídeos capturados
     * @param $data
     * @return mixed
     */
    abstract public function process($data);
}