<?php

class Searcher {

	private $ignoreList = null;

	function __construct(){
		$this->ignoreList = require_once('IgnoreWords.php');
	}

	function getMatchRows($q){
		/** Stings for query */
		$fieldList = 'w0.urlId';
		$tableList = '';
		$clauseList = '';
		$wordList = [];

		$words = explode(' ', strtolower(trim($q)));
		$tableNumber = 0;
		foreach($words as $word){
			if(empty($word)) continue;

			$wordRow = WordList::model()->findByAttributes(array('word'=>$word));
			if( $wordRow ){
				$wordId = $wordRow->id;
				$wordList[] = $wordId;
				if( $tableNumber > 0 ){
					$tableList .= ' , ';
					$clauseList .= ' AND ';
					$clauseList .= sprintf('w%s.urlId = w%s.urlId AND ', $tableNumber-1, $tableNumber);
				}
				$fieldList .= ', w'.$tableNumber.'.location';
				$tableList .= '{{word_location}} w'.$tableNumber;
				$clauseList .= 'w'.$tableNumber.'.wordId = '.$wordId;
				$tableNumber++;
			}
		}
		$fullQuery = "SELECT $fieldList FROM $tableList WHERE $clauseList";
		var_dump($fullQuery);
	}

}