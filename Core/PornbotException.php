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
 * Class BOTException
 * @package PornBOT\Exception
 */
class PornbotException extends \Exception
{
    /**
     * Imprime uma mensagem de erro
     * @return string
     */
	public function __toString()
	{
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}