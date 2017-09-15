<?php
namespace Pornbot\Sites;

class Pornocarioca extends \Pornbot\Core\Sitebase
{
	public function name()
	{
		return 'pornocarioca.com';
	}
	
	public function type()
	{
		return 'html';
	}
	
	public function url()
	{
		return 'http://www.pornocarioca.com/';
	}
	
	public function title()
	{
		return array(
			'pattern' => '/<title>([^\|]+)/i',
            'regexp' => true
		);
	}
	
	public function duration()
	{
		return array(
			'pattern' => '/(\d{2}\:\d{2})\s</',
            'regexp' => true
		);
	}
	
	public function thumbnail()
	{
		return array(
			'pattern' => 'meta[itemprop="thumbnailUrl image"]',
			'attr' => 'content',
            'regexp' => false
		);
	}
	
	public function link()
	{
		return array(
			'pattern' => 'li.list-item a',
			'attr' => 'href',
            'regexp' => false
		);
	}
}