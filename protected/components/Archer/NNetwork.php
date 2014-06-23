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
		$createKey = implode('_', $wordIds);
	}
}