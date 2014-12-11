<?php

class NeyronController extends Controller {

	public function actionIndex(){
		$model = new NNQueryForm;
		$results = null;

		if( isset($_POST['NNQueryForm']) ){
			$model->attributes = $_POST['NNQueryForm'];
			if($model->validate()){
				$searcher = new Searcher;
				$meta = $searcher->getQueryResults($model->query);
				if($meta){
					Yii::app()->session->add('meta', $meta);
					$nn = NNetwork::getInstance();
					$nn->generateHiddenNode($meta['WIds'], $meta['UIds']);
					$results = $nn->getResult($meta['WIds'], $meta['UIds']);
				}
			}
		}

		$this->render('index', [
			'model'	=> $model,
			'results'=> $results
		]);
	}

	public function actionSet($id){
		$meta = Yii::app()->session->get('meta');
		$nn = NNetwork::getInstance();
		$nn->trainQuery($meta['WIds'], $meta['UIds'], $id);
		$url = UrlList::model()->findByPk($id)->url;
		$this->redirect($url);
	}

	public function getUrl($id){
		return UrlList::model()->findByPk($id)->url;
	}

}