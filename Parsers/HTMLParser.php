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
     *
     * @param $url
     *
     * @throws PornbotException
     * @return phpQuery
     */
    private function request($url)
    {
        if ( ! Functions::isValidUrl($url)) {
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
     *
     * @return mixed
     */
    public function prepare_link($link)
    {
        $instance = $this->instance->link;
        if ($instance->regex) {
            return $link;
        }

        return $link->attr($instance->attr);
    }

    /**
     * Prepara os links do parser para a interface de tratamento
     * @return mixed
     */
    public function prepare_links()
    {
        $link = $this->instance->link;

        if ( ! $link->regex) {
            return $this->document->querySelectorAll($link->pattern);
        }

        preg_match_all($link->pattern, $this->document->toString(), $result);

        if (isset($result[1]) && isset($link->path) && $link->path) {
            $url       = $this->instance->url;
            $result[1] = array_map(function ($link) use ($url) {
                return $url . $link;
            }, $result[1]);
        }

        return isset($result[1]) ? $result[1] : [];
    }

    /**
     * Prepara os títulos do parser para a interface de tratamento
     * @return string
     */
    public function prepare_title()
    {
        $title = $this->instance->title;

        if ( ! $title->regex) {
            if ( ! ($node = $this->document->querySelector($title->pattern))) {
                return '';
            }

            return $node->attr($this->instance->attr);
        }

        preg_match($title->pattern, $this->document->toString(), $result);

        return isset($result[1]) ? $result[1] : false;
    }

    /**
     * Prepara o thumbnail do parser para a interface de tratamento
     * @return string
     */
    public function prepare_thumbnails()
    {
        $thumbnail = $this->instance->thumbnail;

        if ( ! $thumbnail->regex) {

            if ( ! ($node = $this->document->querySelectorAll($thumbnail->pattern))) {
                return '';
            }

            return $node->attr($thumbnail->attr);
        }

        preg_match_all($thumbnail->pattern, $this->document->toString(), $result);

        return isset($result[1]) ? $result[1] : false;
    }

    /**
     * Prepara as categorias do parser para a interface de tratamento
     * @return string
     */
    public function prepare_category()
    {
        $category = $this->instance->category;

        if ( ! $category->regex) {

            if ( ! ($node = $this->document->querySelector($category->pattern))) {
                return '';
            }

            return $node->attr($category->attr);
        }

        preg_match($category->pattern, $this->document->toString(), $result);

        return isset($result[1]) ? $result[1] : false;
    }

    /**
     * Prepara o código de incorporação para a interface de tratamento
     * @return bool|string
     */
    private function prepare_embed()
    {
        $embed = $this->instance->embed;

        if ( ! $embed->regex) {

            if ( ! ($node = $this->document->querySelector($embed->pattern))) {
                return '';
            }

            return $node->attr($embed->attr);
        }

        preg_match($embed->pattern, $this->document->toString(), $result);

        if (!isset($result[1])) {
            return false;
        }

        $code = $result[1];

        if (strpos($code, '&lt;') !== false) {
            $code = html_entity_decode($code);
        }

        return $code;
    }

    /**
     * Retorna a instância do exportador
     * @return null|\Pornbot\Export\ExportBase
     * @throws PornbotException
     */
    private function getExportInstance()
    {
        $export_to = Config::get('export_to');
        $export    = null;

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
        $page = $this->getPage();

        $this->document = $this->request($this->instance->random($page));

        $thumbnails = $this->prepare_thumbnails();

        foreach ($this->prepare_links() as $key => $link) {
            $pornurl        = $this->prepare_link($link);
            $this->document = $this->request($pornurl);
            $title          = $this->prepare_title();

            if ($title === false) {
                continue;
            }

            $category = $this->prepare_category();

            if ($category === false) {
                continue;
            }

            if ( ! isset($thumbnails[$key])) {
                continue;
            }

            $thumbnail = $thumbnails[$key];

            $embed = $this->prepare_embed();

            if ($embed === false) {
                continue;
            }

            $data = [
                'title'     => $title,
                'thumbnail' => $thumbnail,
                'link'      => $pornurl,
                'category'  => $category,
                'embed'     => $embed
            ];

            try {
                $this->getExportInstance()->process($data);
            } catch (PornbotException $e) {
                Functions::printlog($e->getMessage());
                break;
            }
        }
    }
}
