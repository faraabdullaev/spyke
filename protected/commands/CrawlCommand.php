<?php

class CrawlCommand extends CConsoleCommand{

	public function run($args){
		$pages = [
			'http://messaki.dev',
			'http://armorgames.dev',
			'http://wc.dev',
			'http://eduaction.dev',
		];

		$spider = Spider::getInstance();

		$spider->run( $pages, 2 );
	}

} 