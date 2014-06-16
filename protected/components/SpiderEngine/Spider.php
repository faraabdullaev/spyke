<?php

Yii::import('application.components.SpiderEngine.*');

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
		echo 'Spider is created' .$this->br;
	}

	/** create or find model */
	function getEntityID( $modelName, $attribute, $value ){
		$attr = [
			$attribute => $value
		];

		$model = $modelName::model()->findByAttributes( $attr );
		if(!$model){
			$model = new $modelName;
			$model->attributes = $attr;
			$model->save();
		}
		return $model->getPrimaryKey();
	}

	/** add one page to index */
	function addToIndex($url){
		if( $this->isIndexed($url) ) return false;
		echo 'Indexing... ' . $url .$this->br;

		/** get words list */
		$text = $this->getTextOnly($url);
		$words = $this->separateWords($text);

		/** get URL id */
		$urlId = $this->getEntityID('UrlList', 'url', $url);

		/** bind words with URL */
		$i = 0;
		foreach($words as $word){
			if( in_array( $word, $this->ignoreList ) ) continue;
			$wodrId = $this->getEntityID('WordList', 'word', $word);
			$model = new WordLocation;
			$model->location = $i;
			$model->urlId = $urlId;
			$model->wordId = $wodrId;
			if($model->save()) $i++;
		}
		return $urlId;
	}

	/** clear HTML text */
	function getTextOnly($url){
		try{
			$content = $this->isDomainAvailible($url, false);
			if( !$content ){
				echo 'Can\'t open : ' . $url . $this->br;
				return;
			}
		}
		catch( Exception $ex ){
			echo 'Can\'t open page' .$this->br;
			return;
		}

		/** remove style */
		$content = preg_replace("/(<style).*style>/sU", "", $content);
		/** remove js */
		$content = preg_replace("/(<script).*script>/sU", "", $content);
		/** remove comments */
		$content = preg_replace("/(<!--).*-->/sU", "", $content);
		/** remove noscript */
		$content = preg_replace("/(<noscript).*noscript>/sU", "", $content);
		/** clear text */
		$content = strip_tags( $content );

		return $content;
	}

	/** separate text to words */
	function separateWords($text){
		$text = iconv('utf-8', 'windows-1251//IGNORE', $text);
		$words = preg_split("/\W/", $text, -1, PREG_SPLIT_NO_EMPTY);
		$new = [];
		foreach($words as $word){
			$new[] = iconv('windows-1251', 'utf-8', strtolower($word));
		}
		return $new;
	}

	/** if this URL in index return TRUE */
	function isIndexed($url){
		$page = UrlList::model()->findByAttributes(array('url'=>$url));

		if($page){
			/** page visited */
			$visit = WordLocation::model()->findByAttributes(array('urlId'=>$page->getPrimaryKey()));
			if($visit)
				return true;
		}
		return false;
	}

	/** all URL from one page to other */
	function addLinkRef($from, $to){
		$model = new Link;
		$model->fromId = $from;
		$model->toId = $to;
		return $model->save();
	}

	function run( $pages, $depth=2 ){
		for($i=0; $i<$depth; $i++){
			$newPages = [];
			foreach($pages as $page){
				try{
					$content = $this->isDomainAvailible($page, false);
					if( !$content ){
						echo 'Can\'t open : ' . $page . $this->br;
						continue;
					}
				}
				catch( Exception $ex ){
					echo 'Can\'t open page' .$this->br;
					continue;
				}
				$fromUrl = $this->addToIndex($page);
				preg_match_all("/<[Aa][\s]{1}[^>]*[Hh][Rr][Ee][Ff][^=]*=[ '\"\s]*([^ \"'>\s#]+)[^>]*>/", $content, $matches);
				$links = $matches[1];
				foreach( $links as $link ){
					/** if link is homepage */
					if( $link == '/' || $link == './' ) continue;

					/** clip all text after # */
					if( strpos($link, '#') != null )
						$link = substr($link, 0, strpos($link, '#') );
					/** if link is local */
					if( strpos($link, '/') == 0 )
						$link = $page . $link ;

					if( strpos($link, 'http')!=null && !$this->isIndexed($link) )
						$newPages[] = $link;

					$toUrl = $this->addToIndex($link);
					if( $toUrl )
						$this->addLinkRef($fromUrl, $toUrl);
				}
			}
			$pages = $newPages;
		}
		echo 'Ending..';
	}

	function isDomainAvailible($url, $headerOnly = true){
		if(!filter_var($url, FILTER_VALIDATE_URL)) return false;

		$curlInit = curl_init($url);
		curl_setopt($curlInit,CURLOPT_CONNECTTIMEOUT,10);
		curl_setopt($curlInit,CURLOPT_HEADER,true);
		curl_setopt($curlInit,CURLOPT_RETURNTRANSFER,true);

		if($headerOnly)
			curl_setopt($curlInit,CURLOPT_NOBODY,true);

		$response = curl_exec($curlInit);

		curl_close($curlInit);

		if ($response) return $response;

		return false;
	}

	function calculatePageRank($iterations = 20){
		echo 'Remove old records'.$this->br;
		PageRank::model()->deleteAll('1');

		echo 'Make clones'.$this->br;
		$urlList = UrlList::model()->findAll();
		foreach($urlList as $item){
			$model = new PageRank;
			$model->urlId = $item->id;
			$model->score = 1;
			$model->save();
		}

		echo 'Start iteration'.$this->br;
		$db =  Yii::app()->db;
		for($i=1; $i<=$iterations; $i++){
			echo 'iter. #'.$i.$this->br;
			foreach($urlList as $item){
				$pr = 0.15;
				$links = Link::model()->findAllByAttributes(['toId'=>$item->id]);
				foreach($links as $link){
					$linkPR = PageRank::model()->findByAttributes(['urlId'=>$link->fromId]);
					if(!$linkPR) continue;
					$linkPR = $linkPR->score;
					$linkCounts = Link::model()->countByAttributes(['fromId'=>$link->fromId]);
					$pr += 0.85*($linkPR/$linkCounts);
				}
				$db->createCommand()->update('{{page_rank}}', array('score'=>$pr), 'urlId = :urlId', array(':urlId'=>$item->id));
			}
		}
		echo 'PageRank calculate end.'.$this->br;
	}
}