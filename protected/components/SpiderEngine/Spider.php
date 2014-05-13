<?php

Yii::import('application.components.SpiderEngine.*');
Yii::import('application.components.SpiderEngine.htmlParser.*');

class Spider{

	private $br = "\n";
	private $ignoreList = null;
	/** spider - instance */
	static private $spider = null;
	/** protected with singtone */
	static public function getInstance(){
		if(self::$spider == null)
			self::$spider = new Spider();
		return self::$spider;
	}

	/** only for php */
	function __clone(){}
	/** hide constructor */
	private function __construct(){
		$this->ignoreList = require_once('IgnoreWords.php');
		echo 'Spider is create' .$this->br;
	}

	/** create or find model */
	function getEntityID(){
		return;
	}

	/** add one page to index */
	function addToIndex($url){
		echo 'Indexing... ' . $url .$this->br;
	}

	/** clear HTML text */
	function getTextOnly($html){
		return;
	}

	/** separate text to words */
	function separateWords($text){
		return;
	}

	/** if this URL in index return TRUE */
	function isIndexed($url){
		return false;
	}

	/** all URL from one page to other */
	function addLinkRef($from, $to, $text){
		return;
	}

	function run( $pages, $depth=2 ){
		for($i=0; $i<$depth; $i++){
			$newPages = [];
			foreach($pages as $page){
				try{
					$content = file_get_contents($page);
				}
				catch( Exception $ex ){
					echo 'Can\'t open page' .$this->br;
					continue;
				}
				$this->addToIndex($page);
				preg_match_all("/<[Aa][\s]{1}[^>]*[Hh][Rr][Ee][Ff][^=]*=[ '\"\s]*([^ \"'>\s#]+)[^>]*>/", $content, $matches);
				$links = $matches[1];
				foreach( $links as $link ){
					/** clip all text after # */
					if( strpos($link, '#') != null )
						$link = substr($link, 0, strpos($link, '#') );
					/** if link is local */
					if( strpos($link, '/') == 0 )
						$link = $page . $link ;

					if( strpos($link, 'http')!=null && !$this->isIndexed($link) )
						$newPages[] = $link;

					$linkText = $this->getTextOnly($link);
					$this->addLinkRef($page, $link, $linkText);
				}
			}
			echo $this->br;
			$pages = $newPages;
		}
	}

}