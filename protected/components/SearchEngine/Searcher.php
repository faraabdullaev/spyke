<?php

class Searcher {

	private $_words = null;
	function __construct(){
		return true;
	}

	private function getMatchRows($q){
		/** Stings for query */
		$fieldList = 'w0.urlId';
		$tableList = '';
		$clauseList = '';
		$wordList = [];
		$fullQuery = null;
		$words = explode(' ', strtolower(trim($q)));
		$tableNumber = 0;
		if(count($words)==1){
			$wordRow = WordList::model()->findByAttributes(array('word'=>$words[0]));
			if(!$wordRow) return;
			$wordId = $wordRow->id;
			$wordList[] = $wordId;
			if( $wordRow )
				$fullQuery = "SELECT w0.urlId, w0.location AS s1 FROM spk_word_location w0 WHERE w0.wordId = $wordId";
		} else {
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
					$fieldList .= sprintf(', w%s.location  AS s%s', $tableNumber, $tableNumber);
					$tableList .= sprintf('{{word_location}} w%s', $tableNumber);
					$clauseList .= sprintf('w%s.wordId = %s ', $tableNumber, $wordId);
					$tableNumber++;
				}
			}
			$fullQuery = sprintf('SELECT %s FROM %s WHERE %s GROUP BY w0.urlId', $fieldList, $tableList, $clauseList);
		}
		if(!$fullQuery) return;
		$command = Yii::app()->db->createCommand($fullQuery);
		$positions = $command->queryAll();

		$this->_words = $wordList;
		return [ 'positions'	=> $positions, 'wordList'	=> $wordList ];
	}

	private function calculateScores($rows, $wordIds, $method){
		switch ($method){
			case QueryForm::METHOD_LOCATION:
				return $this->locationScore($rows);
			case $method == QueryForm::METHOD_DISTANCE:
				return $this->distanceScore($rows);
			case $method == QueryForm::METHOD_FREQUENCY:
				return $this->frequencyScore($rows);
			case $method == QueryForm::METHOD_PAGERANK:
				return $this->pagerankScore($rows);
			case $method == QueryForm::METHOD_NEURAL_NETWORK:
				return $this->nueronnetworkScore();
		}
	}

	private function getQueryResults($q){
		$results = $this->getMatchRows($q);
		$WIds = $results['wordList'];
		$UIds = [];
		foreach($results['positions'] as $pos)
			$UIds[] = $pos['urlId'];
		Yii::app()->session->add('meta', ['WIds'=>$WIds, 'UIds'=>$UIds]);
		return $results;
	}

	function Query($query, $method){
		$result = $this->getQueryResults($query);
		if(!$result) return;
		return $this->calculateScores($result['positions'], $result['wordList'], $method);
	}

	private function normalize($rows, $smallIsBetter = true){
		if(!$rows) return;
		$vsmall = 0.0001;
		$list = [];
		if( $smallIsBetter ){
			$minScore = min($rows);
			foreach($rows as $url => $score)
				$list[ $url ] = (float)$minScore/max($vsmall, $score);
			asort($list);
		} else {
			$maxScore = max($rows);
			if($maxScore == 0) $maxScore = $vsmall;
			foreach($rows as $url => $score)
				$list[ $url ] = (float)$score/$maxScore;
			arsort($list);
		}
		return $list;
	}

	private function nueronnetworkScore(){
		$meta = Yii::app()->session->get('meta');
		$nn = NNetwork::getInstance();
		$nn->generateHiddenNode($meta['WIds'], $meta['UIds']);
		$rows = $nn->getResult($meta['WIds'], $meta['UIds']);
		return $this->normalize($rows, false);
	}

	private function frequencyScore($rows){
		$counts = [];
		foreach($rows as $row){
			$sum = 0;
			foreach($this->_words as $word){
				$sum += WordLocation::model()->count("wordId = $word AND urlId = ".$row['urlId']);
			}
			$counts[ $row['urlId'] ] = $sum;
		}
		return $this->normalize($counts, false);
	}

	private function locationScore($rows){
		$locations = $sum = [];
		$i = 0;
		foreach($rows as $row){
			foreach($row as $key => $val){
				$sum[$i] = 0;
				if($key == 'urlId') continue;
				$sum[$i] += $val;
				$i++;
			}
		}
		$i = 0;
		foreach($rows as $row){
			$locations[ $row['urlId'] ] = 1000000;
			if( $sum[$i] < $locations[ $row['urlId'] ] )
				$locations[ $row['urlId'] ] = $sum[$i];
			$i++;
		}
		return $this->normalize($locations);
	}

	private function distanceScore($rows){
		$minDist = [];
		foreach($rows as $row){
			$minDist[ $row['urlId'] ] = 1000000;
			$dist = [];
			$sum = 0;
			foreach($row as $key => $val){
				if($key == 'urlId') continue;
				$dist[] = $val;
			}
			for($i=1; $i<count($dist);$i++){
				$sum += abs($dist[$i] - $dist[$i-1]);
			}
			if( $sum < $minDist[ $row['urlId'] ] )
				$minDist[ $row['urlId'] ] = $sum;
		}
		return $this->normalize($minDist);
	}

	private function pagerankScore($rows){
		$pageranks = [];
		foreach($rows as $row){
			$item = PageRank::model()->findByAttributes(['urlId'=>$row['urlId']]);
			$pageranks[ $row['urlId'] ] = $item->score;
		}
		return $this->normalize($pageranks);
	}

}