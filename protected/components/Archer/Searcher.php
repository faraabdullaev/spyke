<?php

class Searcher {

	private $ignoreList = null;
	private $_words = null;
	function __construct(){
		$this->ignoreList = require_once('IgnoreWords.php');
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

		$result = [
			'positions'	=> $positions,
			'wordList'	=> $wordList
		];
		$this->_words = $wordList;
		return $result;
	}

	private function getUrlName($id){
		$model = UrlList::model()->findByPk($id);
		if( $model )
			return $model->url;
		return false;
	}

	private function getScoredList($rows, $wordIds, $method){
		$totalScores = [];

		$algo = [];
		switch($method){
			case 1:
				$algo[] = $this->locationScore($rows);
				break;
			case 1:
				$algo[] = $this->distanceScore($rows);
				break;
			case 1:
				$algo[] = $this->frequencyScore($rows);
				break;
			default:
				$algo[] = $this->pagerankScore($rows);
		}
		$weights = $this->getWeight( $algo );

		foreach($weights as $weight => $scores)
			$totalScores[] = [ $weight, $scores ];

		return $totalScores;
	}

	function Query($query, $method){
		$result = $this->getMatchRows($query);
		if(!$result) return;
		$rows = $result['positions'];
		$wordList = $result['wordList'];

		$scores = $this->getScoredList($rows, $wordList, $method);
		for($i=0; $i<count($scores); $i++){
			$scores[$i][0] = $this->getUrlName($scores[$i][1][0]);
		}

		$rankedScores = [];
		foreach($scores as $score){
			$rankedScores[] = [
					'score'=> $score[1][1],
					'url'	=> $score[0]
				];
		}
		return $rankedScores;
	}

	private function normalize($rows, $smallIsBetter = true){
		if(!$rows) return;
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
		$counts = [];
		foreach($rows as $row){
			$sum = 0;
			foreach($this->_words as $word){
				$sum += WordLocation::model()->count("wordId = $word AND urlId = ".$row['urlId']);
			}
			$counts[] = [
				$row['urlId'],
				$sum
			];
		}
		return $this->normalize($counts, false);
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

	private function distanceScore($rows){
		$minDist = [];
		$j = 0;
		foreach($rows as $row){
			$minDist[] = [$row['urlId'], 1000000];
			$dist = [];
			$sum = 0;
			foreach($row as $key => $val){
				if($key == 'urlId') continue;
				$dist[] = $val;
			}
			for($i=1; $i<count($dist);$i++){
				$sum += abs($dist[$i] - $dist[$i-1]);
			}
			if( $sum < $minDist[$j][1] )
				$minDist[$j][1] = $sum;
			$j++;
		}
		return $this->normalize($minDist);
	}

	private function pagerankScore($rows){
		$pageranks = [];
		foreach($rows as $row){
			$item = PageRank::model()->findByAttributes(['urlId'=>$row['urlId']]);
			$pageranks[] = [ $row['urlId'], $item->score ];
		}
		return $this->normalize($pageranks);
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

	private function getWeight($arr){
		$list = [];
		foreach($arr as $metric => $results){
			foreach( $results as $position ){
				if (!isset($list[$position[0]]))
					$list[ $position[ 0 ] ] = $position[1];
				else
					$list[ $position[ 0 ] ] += $position[1];
			}
		}
		$results = [];
		$max = max( $list );
		asort( $list );
		foreach($list as $key => $score) $results[] = [ $key, (float)$score/$max ];
		return $results;
	}
}