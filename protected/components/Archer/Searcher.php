<?php

class Searcher {

	private $ignoreList = null;

	function __construct(){
		$this->ignoreList = require_once('IgnoreWords.php');
	}

	private function getMatchRows($q){
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
				$fieldList .= sprintf(', w%s.location  AS s%s', $tableNumber, $tableNumber);
				$tableList .= sprintf('{{word_location}} w%s', $tableNumber);
				$clauseList .= sprintf('w%s.wordId = %s ', $tableNumber, $wordId);
				$tableNumber++;
			}
		}
		$fullQuery = sprintf('SELECT %s FROM %s WHERE %s', $fieldList, $tableList, $clauseList);
		$command = Yii::app()->db->createCommand($fullQuery);
		$positions = $command->queryAll();

		$result = [
			'positions'	=> $positions,
			'wordList'	=> $wordList
		];

		return $result;
	}

	private function getUrlName($id){
		$model = UrlList::model()->findByPk($id);
		if( $model )
			return $model->url;
		return false;
	}

	private function getScoredList($rows, $wordIds){
		$totalScores = [];
		foreach($rows as $row){
			$totalScores[] = [$row['urlId'], 0];
		}
		/** TODO::Сюда функцию ранжирования */
		$weights = $this->locationScore($rows);

		foreach($weights as $weight => $scores){
			for($i = 0; $i<count($totalScores); $i++){
				$totalScores[$i] = $scores;
			}
		}
		return $totalScores;
	}

	function Query($q){
		$result = $this->getMatchRows($q);
		$rows = $result['positions'];
		$wordList = $result['wordList'];

		$scores = $this->getScoredList($rows, $wordList);
		for($i=0; $i<count($scores); $i++){
			$scores[$i][0] = $this->getUrlName($scores[$i][0]);
		}

		$rankedScores = [];
		foreach($scores as $score){
			$rankedScores[] = [
					'scores'=> $score[1],
					'url'	=> $score[0]
				];
		}
		$this->frequencyScore($rows);
	}

	private function normalize($rows, $smallIsBetter = true){
		$vsmall = 0.0001;
		$scoresValues = $this->getScoresValues($rows);
		$list = [];
		if( $smallIsBetter ){
			$minScore = min($scoresValues);
			foreach($rows as $row){
				$list[] = [
					$row[0],
					(float)$minScore/max($vsmall, $row[1])
				];
			}
		} else {
			$maxScore = max($scoresValues);
			if($maxScore == 0) $maxScore = $vsmall;
			foreach($rows as $row){
				$list[] = [
					$row[0],
					(float)$row[1]/$maxScore
				];
			}
		}
		return $list;
	}

	private function frequencyScore($rows){
		$count = 0;
		$i = 0;
		$counts = [];
		foreach($rows as $row){

		}
		var_dump($counts); echo '<br>';
		//return $this->normalize();
	}

	private function locationScore($rows){
		$locations = [];
		$sum = $this->getScoresValues($rows);
		$i = 0;
		foreach($rows as $row){
			$locations[$i] = [$row['urlId'], 1000000];
			if( $sum[$i] < $locations[$i][1] )
				$locations[$i][1] = $sum[$i];
			$i++;
		}
		return $this->normalize($locations);
	}

	private function getScoresValues($rows){
		$list = [];
		$i = 0;
		foreach($rows as $row){
			foreach($row as $key => $val){
				$list[$i] = 0;
				if($key == 'urlId') continue;
				$list[$i] += $val;
				$i++;
			}
		}
		return $list;
	}
}