<?php

class CrawlCommand extends CConsoleCommand{

	public function run($args){
		$pages = [
			'http://messaki.dev/',
			'http://wc.dev/',
		];

		$spider = Spider::getInstance();

		$spider->run( $pages );
	}

} 