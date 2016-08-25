<?php
/**
 * Version information
 *
 * @package    pornbot
 * @copyright  2016 Joseph Felix
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace PornBOT\Parsers;

global $CFG;
require_once($CFG->libdir . '/curl/Curl.php');
require_once($CFG->libdir . '/phpquery/PHPQuery.php');

use \Curl\Curl;
use PornBOT\BOT as BOT;
use PornBOT\Exception\BOTException as BotException;

/**
 * Class HTMLParser
 * @package PornBOT\Parsers
 */
class HTMLParser
{
    /**
     * DOMElement da página
     * @var phpQueryObject
     */
    private $document;

    /**
     * Busca o DOMElement html a partir de um link
     *
     * @param $url
     * @return \phpQueryObject|\QueryTemplatesParse|\QueryTemplatesSource|\QueryTemplatesSourceQuery
     * @throws BotException
     */
    private function getDocument($url)
    {
        $curl = new Curl();
        $curl->setUserAgent('Pornbot v1.0');
        $curl->setOpt(CURLOPT_FOLLOWLOCATION, true);
        $curl->setOpt(CURLOPT_RETURNTRANSFER, true);
        $curl->setOpt(CURLOPT_SSL_VERIFYHOST, false);
        $curl->setOpt(CURLOPT_SSL_VERIFYPEER, false);
        $curl->get($url);
        if ($curl->error) {
            throw new BOTException('Link inválido: ' . $url);
        }

        return \phpQuery::newDocumentHTML($curl->response);
    }

    /**
     * Inicia o bot
     *
     * @param BOT $instance
     * @param $export
     * @return null
     */
    public function start(BOT $instance, $export)
    {
        $this->document = $this->getDocument($instance->url());
        $url = (object)$instance->link();

        if (!$url->regexp) {
            $links = $this->document->find($url->pattern)->elements;
        } else {
            preg_match_all($url->pattern, $this->document->html(), $links);
            $links = isset($links[1]) ? $links[1] : array();
        }

        foreach ($links as $link) {
            try {
                $pornurl = $link->getAttribute($url->attr);
                $this->document = $this->getDocument($pornurl);

                $titleinstance = (object)$instance->title();
                $durationinstance = (object)$instance->duration();
                $thumbinstance = (object)$instance->thumbnail();

                if (!$titleinstance->regexp) {

                    if ($title = $this->document->find($titleinstance->pattern)->get(0)) {
                        $title = $title->getAttribute($titleinstance->attr);
                    }

                } else {
                    preg_match($titleinstance->pattern, $this->document->html(), $title);
                    $title = isset($title[1]) ? $title[1] : false;
                }

                if (!$durationinstance->regexp) {
                    if ($duration = $this->document->find($durationinstance->pattern)->get(0)) {
                        $duration = $duration->getAttribute($durationinstance->attr);
                    }
                } else {
                    preg_match($durationinstance->pattern, $this->document->html(), $duration);
                    $duration = isset($duration[1]) ? $duration[1] : false;
                }

                if (!$thumbinstance->regexp) {
                    if ($thumbnail = $this->document->find($thumbinstance->pattern)->get(0)) {
                        $thumbnail = $thumbnail->getAttribute($thumbinstance->attr);
                    }
                } else {
                    preg_match($thumbinstance->pattern, $this->document->html(), $thumbnail);
                    $thumbnail = isset($thumbnail[1]) ? $thumbnail[1] : false;
                }

                if ($thumbnail && $title && $duration) {

                    $data = array(
                        'title' => $title,
                        'duration' => $duration,
                        'thumbnail' => $thumbnail,
                        'link' => $pornurl
                    );

                    foreach ($data as $index => &$row) {
                        $row = call_user_func_array("\\PornBOT\\Format::format_{$index}", [$row, $instance]);
                    }

                    $export->process($data);
                }
            } catch (BOTException $err) {
                printlog($err->getMessage());
            }

        }
    }
}
