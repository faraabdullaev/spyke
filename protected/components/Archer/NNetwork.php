<?php

class NNetwork {

	private $wordIds;
	private $urlIds;
	private $hiddenIds;
	private $ai = [];
	private $ah = [];
	private $ao = [];
	private $wi = [];
	private $wo = [];

	function __construct(){
		return true;
	}

	function getStrength($fromId, $toId, $layer){
		$model = $this->getStrengthModel($layer);
		$result = $model->findByAttributes([ 'fromId' => $fromId, 'toId' => $toId ]);
		if( !$result ){
			if($layer == 0) return -0.2;
			else return 0;
		}
		return $result->strength;
	}

	function setStrength($fromId, $toId, $layer, $strength){
		$model = $this->getStrengthModel($layer);
		$result = $model->findByAttributes([ 'fromId' => $fromId, 'toId' => $toId ]);
		if( !$result ) {
			if($layer == 0) $model = new WordHd;
			else $model = new UrlHd;
			$model->fromId = $fromId;
			$model->toId = $toId;
		}
		$model->strength = $strength;
		$model->save();
	}

	private function getStrengthModel($layer){
		if($layer == 0) return WordHd::model();
		else return UrlHd::model();
	}

	function generateHiddenNode($wordIds, $urls){
		if( count($wordIds) > 3 ) return null;
		$create_key = implode('_', $wordIds);
		$result = NodeHd::model()->findByAttributes(['create_key'=>$create_key]);
		if( !$result ){
			$result = new NodeHd;
			$result->create_key = $create_key;
			$result->save();
			$hdID = $result->getPrimaryKey();
			$strength = 1.0/count($wordIds);
			foreach($wordIds as $id)
				$this->setStrength($id, $hdID, 0, $strength);
			foreach($urls as $id)
				$this->setStrength($hdID, $id, 1, 0.1);
		}
	}

	function getAllHiddenIds($worddIds, $urlIds){
		$l1 = [];
		foreach( $worddIds as $id ){
			$words = WordHd::model()->findAllByAttributes(['fromId'=>$id]);
			foreach($words as $word)
				$l1[ $word->toId ] = 1;
		}
		foreach( $urlIds as $id){
			$urls = UrlHd::model()->findAllByAttributes(['toId'=>$id]);
			foreach($urlIds as $url)
				$l1[ $url->fromId ] = 1;
		}
		return array_keys($l1);
	}

	function setupNetwork($wordIds, $urlIds){
		$this->wordIds = $wordIds;
		$this->urlIds = $urlIds;
		$this->hiddenIds = $this->getAllHiddenIds($wordIds, $urlIds);

		//$this->ai = 1.0*count($this->wordIds);
		//$this->ah = 1.0*count($this->hiddenIds);
		//$this->ao = 1.0*count($this->urlIds);

		/** Создаем матрицу весов */
		$i = 0;
		$j = $i;
		foreach($this->wordIds as $wid){
			foreach($this->hiddenIds as $hid){
				$this->wi[$i][$j] = $this->getStrength($wid, $hid, 0);
				$j++;
			}
			$j = 0;
			$i++;
		}
		foreach($this->hiddenIds as $hid){
			foreach($this->urlIds as $uid){
				$this->wo[$i][$j] = $this->getStrength($hid, $uid, 1);
				$j++;
			}
			$j = 0;
			$i++;
		}
	}

	function FeedForward(){
		for($i=0; $i<count($this->wordIds); $i++) $this->ai[$i] = 1.0;
		/** Возбуждение скрытых узлов */
		for($j=0; $j<count($this->hiddenIds); $j++){
			$sum = 0.0;
			for($i=0; $i<count($this->wordIds); $i++){
				$sum += $this->ai[$i] * $this->wi[$i][$j];
			}
			$this->ah[$j] = tanh($sum);
		}
		/** Возбуждение выходных узлов */
		for($k=0; $k<count($this->urlIds); $k++){
			$sum = 0.0;
			for($j=0; $j<count($this->hiddenIds); $j++){
				$sum += $this->ah[$j] * $this->wo[$j][$k];
			}
			$this->ao[$k] = tanh($sum);
		}
		return $this->ao;
	}

}