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
use Pornbot\Core\PornbotException;
use Pornbot\Core\Functions;
use Pornbot\Core\Config;

/**
 * Class HTMLParser
 * @package Pornbot\Parsers
 */
class HTMLParser extends Parserbase
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
     * Prepara o link do parser para a interface de tratamento
     *
     * @param $link
     * @return mixed
     */
    public function prepare_link($link)
    {
        $instance = $this->instance->link;
        return $link->attr($instance->attr);
    }

    /**
     * Prepara os links do parser para a interface de tratamento
     *
     * @param $url
     * @return mixed
     */
    public function prepare_links()
    {
        $link = $this->instance->link;

        if (!$link->regexp) {
            return $this->document->querySelectorAll($link->pattern);
        }

        preg_match_all($link->pattern, $this->document->toString(), $result);
        return isset($result[1]) ? $result[1] : [];
    }

    /**
     * Prepara os títulos do parser para a interface de tratamento
     * @return string
     */
    public function prepare_title()
    {
        $title = $this->instance->title;

        if (!$title->regexp) {
            if (!($node = $this->document->querySelector($title->pattern))) {
                return '';
            }

            return $node->attr($this->instance->attr);
        }

        preg_match($title->pattern, $this->document->toString(), $result);
        return isset($result[1]) ? $result[1] : false;
    }

    /**
     * Prepara as durações do parser para a interface de tratamento
     * @return string
     */
    public function prepare_duration()
    {
        $duration = $this->instance->duration;

        if (!$duration->regexp) {
            if (!($node = $this->document->querySelector($duration->pattern))) {
                return '';
            }

            return $node->attr($duration->attr);
        }

        preg_match($duration->pattern, $this->document->toString(), $result);
        return isset($result[1]) ? $result[1] : false;
    }

    /**
     * Prepara o thumbnail do parser para a interface de tratamento
     * @return string
     */
    public function prepare_thumbnail()
    {
        $thumbnail = $this->instance->thumbnail;

        if (!$thumbnail->regexp) {

            if (!($node = $this->document->querySelector($thumbnail->pattern))) {
                return '';
            }

            return $node->attr($thumbnail->attr);
        }

        preg_match($thumbnail->pattern, $this->document->toString(), $result);
        return isset($result[1]) ? $result[1] : false;
    }

    /**
     * Retorna a instância do exportador
     * @return null|\Pornbot\Export\ExportBase
     */
    private function getExportInstance()
    {
        $export_to = Config::get('export_to');
        $export = null;

        switch ($export_to) {
            case 'wordpress':
                $export = new \Pornbot\Export\Wordpress;
                break;
            case 'database':
                $export = new \Pornbot\Export\Database;
                break;
            default:
                throw new PornbotException('Configuração "export_to" errada, use wordpress ou database para continuar.');
        }

        return $export;
    }

    /**
     * Inicia o bot
     * @return null
     */
    public function start()
    {
        $this->document = $this->request($this->instance->url);

        foreach ($this->prepare_links() as $link) {
            $pornurl = $this->prepare_link($link);
            $this->document = $this->request($pornurl);
            $title = $this->prepare_title();
            $duration = $this->prepare_duration();
            $thumbnail = $this->prepare_thumbnail();

            if ($title && $duration && $thumbnail) {
                $data = [
                    'title'     => $title,
                    'duration'  => $duration,
                    'thumbnail' => $thumbnail,
                    'link'      => $pornurl
                ];

                try {
                    $this->getExportInstance()->process($data);
                } catch (PornbotException $e) {
                    Functions::printlog('Erro ao instanciar o exportador.');
                    break;
                }
            }
        }
    }
}
