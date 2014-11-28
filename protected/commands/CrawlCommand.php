<?php

class CrawlCommand extends CConsoleCommand{

	public function actionRun(){
		$pages = [
			'http://lex.uz',
			'https://my.gov.uz/uz/type/6',
			'http://soliq.uz/uz/',
			'http://customs.uz/uz/',
			'http://bank.uz/',
			'http://norma.uz/',
		];

		$spider = Spider::getInstance();

		$spider->run( $pages, 2 );
	}

	public function actionPagerank(){
		$spider = Spider::getInstance();
		$spider->calculatePageRank();
	}
}