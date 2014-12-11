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

	static private $NEURAL_NETWORK = null;
	static public function getInstance(){
		if(self::$NEURAL_NETWORK == null) self::$NEURAL_NETWORK = new NNetwork;
		return self::$NEURAL_NETWORK;
	}
	function __clone(){}
	private function __construct(){
		return true;
	}

	function getStrength($fromId, $toId, $layer){
		$model = $this->getStrengthModel($layer);
		$result = $model->findByAttributes([ 'fromId' => $fromId, 'toId' => $toId ]);
		if( !$result )
			return ($layer == 0) ? -0.2 : 0.0;
		return $result->strength;
	}

	function setStrength($fromId, $toId, $layer, $strength){
		$model = $this->getStrengthModel($layer);
		$model = $model->findByAttributes([ 'fromId' => $fromId, 'toId' => $toId ]);
		if( !$model ) {
			$model = ($layer == 0) ? new WordHd : new UrlHd;
			$model->fromId = $fromId;
			$model->toId = $toId;
		}
		$model->strength = $strength;
		$model->save();
	}

	private function getStrengthModel($layer){
		return ($layer == 0) ? WordHd::model() : UrlHd::model();
	}

	function generateHiddenNode($wordIds, $urls){
		if( count($wordIds) > 5 ) return null;
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
			foreach($urls as $url)
				$l1[ $url->fromId ] = 1;
		}
		return array_keys($l1);
	}

	function setupNetwork($wordIds, $urlIds){
		$this->wordIds = $wordIds;
		$this->urlIds = $urlIds;
		$this->hiddenIds = $this->getAllHiddenIds($wordIds, $urlIds);

		/** Создаем матрицу весов */
		$i = $j = 0;
		foreach($this->wordIds as $wid){
			foreach($this->hiddenIds as $hid){
				$this->wi[$i][$j] = $this->getStrength($wid, $hid, 0);
				$j++;
			}
			$j = 0;
			$i++;
		}
		$i = $j = 0;
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

	private function dtanh($x){
		return 1.0-$x*$x;
	}

	function BackPropagade($targets, $N = 0.5){
		/** Вычислить поправки для выходного сигнала */
		$output_deltas = [];
		for($i=0; $i<count($this->urlIds); $i++){
			$error = $targets[$i] - $this->ao[$i];
			$output_deltas[$i] = $this->dtanh($this->ao[$i]) * $error;
		}
		/** Вычислить поправки для скрытого сигнала */
		$hidden_deltas = [];
		for($j=0; $j<count($this->hiddenIds); $j++){
			$error = 0.0;
			for($i=0; $i<count($this->urlIds); $i++){
				$error += $output_deltas[$i] * $this->wo[$j][$i];
			}
			$hidden_deltas[$j] = $this->dtanh($this->ah[$j]) * $error;
		}
		/** Обновления веса связей между узлами скрытого и выходного слоя */
		for($j=0; $j<count($this->hiddenIds); $j++){
			for($i=0; $i<count($this->urlIds); $i++){
				$change = $output_deltas[$i] * $this->ah[$j];
				$this->wo[$j][$i] += $N * $change;
			}
		}
		/** Обновления веса связей между узлами входного и скрытого слоя */
		for($i=0; $i<count($this->wordIds); $i++){
			for($j=0; $j<count($this->hiddenIds); $j++){
				$change = $hidden_deltas[$j] * $this->ai[$i];
				$this->wi[$i][$j] += $N * $change;
			}
		}
	}

	function trainQuery($wordIds, $urlIds, $selectedUrl){
		/** Сгенерировать скрытый узел если необходимо */
		$this->generateHiddenNode($wordIds, $urlIds);

		$this->setupNetwork($wordIds, $urlIds);
		$this->FeedForward();
		$targets = [];
		foreach($urlIds as $url)
			$targets[] = (float)($url == $selectedUrl);
		$this->BackPropagade($targets);
		$this->updateDB();
	}

	function updateDB(){
		$i = $j = 0;
		foreach($this->wordIds as $wid){
			foreach($this->hiddenIds as $hid){
				$this->setStrength($wid, $hid, 0, $this->wi[$i][$j]);
				$j++;
			}
			$j = 0;
			$i++;
		}
		$i = $j = 0;
		foreach($this->hiddenIds as $hid){
			foreach($this->urlIds as $uid){
				$this->setStrength($hid, $uid, 1, $this->wo[$i][$j]);
				$j++;
			}
			$j = 0;
			$i++;
		}
	}

	public function getResult($wordIds, $urlIds){
		$this->setupNetwork($wordIds, $urlIds);
		$ff = $this->FeedForward();
		$i = 0;
		$results = [];
		foreach($ff as $res){
			$results[ $urlIds[$i] ] = $res;
			$i++;
		}
		return $results;
	}
}