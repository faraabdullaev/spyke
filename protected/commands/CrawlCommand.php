<?php

class CrawlCommand extends CConsoleCommand{

	public function actionRun(){
		$pages = [
			'http://messaki.dev',
			'http://armorgames.dev',
			'http://wc.dev',
			'http://eduaction.dev',
		];

		$spider = Spider::getInstance();

		$spider->run( $pages, 2 );
	}

	public function actionPagerank(){
		$spider = Spider::getInstance();
		$spider->calculatePageRank();
	}
}