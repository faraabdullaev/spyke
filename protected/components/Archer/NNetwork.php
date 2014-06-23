<?php

class NNetwork {

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
}