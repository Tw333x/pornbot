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
 * Class Bootstrap
 * @package Pornbot
 */
class Loader
{
    /**
     * Inicia e executa o bot
     * @param array $classes
     * @throws PornbotException
     */
    public static function init(array $classes)
    {
        $instance = $classes['class'];

        echo 'Iniciando para o site: ', $instance->name(), PHP_EOL;

        $parser = null;
        switch ($instance->type()) {
            case 'xml':
                $parser = new \Pornbot\Parsers\XMLParser;
                break;
            case 'html':
                $parser = new \Pornbot\Parsers\HTMLParser;
                break;
            case 'json':
                $parser = new \Pornbot\Parsers\JSONParser;
                break;
            default:
        }

        if ($parser === null) {
            throw new \Pornbot\Core\PornbotException('Parser não encontrado, por favor utilize xml, html ou json no método type() de seu site.');
        }

        $parser->setInstance($instance);
        $parser->start();
    }
}