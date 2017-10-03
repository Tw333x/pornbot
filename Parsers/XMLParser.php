<?php
/**
 * Version information
 *
 * @package    pornbot
 * @copyright  2017 Joseph Felix
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace Pornbot\Parsers;

use Curl\Curl;
use Jclyons52\PHPQuery\Document as phpQuery;
use Pornbot\Core\Functions;
use Pornbot\Core\PornbotException;

/**
 * Class XMLParser
 * @package Pornbot\Parsers
 */
class XMLParser extends Parserbase
{
    /**
     * @var phpQuery
     */
    private $document;

    /**
     * Retorna a instância do phpquery para o documento da página
     * @param $url
     * @throws PornbotException
     * @return phpQuery
     */
    private function request($url)
    {
        if (!Functions::isValidUrl($url)) {
            throw new PornbotException('Link inválido: ' . $url);
        }

        $curl = new Curl();
        $curl->setUserAgent('Pornbot v2.0');
        $curl->setHeader('X-Requested-With', 'Pornbot v2.0');
        $curl->setOpt(CURLOPT_FOLLOWLOCATION, true);
        $curl->setOpt(CURLOPT_RETURNTRANSFER, true);
        $curl->setOpt(CURLOPT_SSL_VERIFYHOST, false);
        $curl->setOpt(CURLOPT_SSL_VERIFYPEER, false);
        $curl->get($url);

        if ($curl->error) {
            throw new PornbotException('Link inválido: ' . $url);
        }

        return new phpQuery($curl->response);
    }

    /**
     * Inicia o bot
     * @return null
     */
    public function start()
    {
        $instance = $this->getInstance();
    }
}