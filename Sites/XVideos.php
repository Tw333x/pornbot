<?php
namespace Pornbot\Sites;

class XVideos extends \Pornbot\Core\Sitebase
{
    public function name()
    {
        return 'xvideos.com';
    }

    public function type()
    {
        return 'html';
    }

    public function random($page)
    {
        return 'https://www.xvideos.com/new/' . $page . '/';
    }

    public function url()
    {
        return 'https://www.xvideos.com';
    }

    public function title()
    {
        return [
            'pattern' => '/<title>([^\<]+)<\/title>/i',
            'regex'   => true
        ];
    }

    public function thumbnail()
    {
        return [
            'pattern' => '/<img\ssrc=\"([^\"]+)\"\sid=\"pic\_/i',
            'regex'   => true
        ];
    }

    public function link()
    {
        return [
            'pattern' => '/<p><a\s*href="(\/video\d+[^\"]+)\"\s*title=/i',
            'regex'   => true,
            'path'    => true
        ];
    }

    public function category()
    {
        return [
            'pattern' => '/window\.wpn\_categories\s=\s\"([^\"]+)/i',
            'regex'   => true
        ];
    }

    public function embed()
    {
        return [
            'pattern' => '/this\.focus\(\)\;\sthis\.select\(\);\"\svalue=\'([^\']+)/i',
            'regex'   => true
        ];
    }
}