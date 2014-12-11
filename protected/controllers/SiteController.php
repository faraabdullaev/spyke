<?php

class SiteController extends Controller {

	public function actionIndex(){
		$model = new QueryForm;
		$results = null;
		if( isset($_POST['QueryForm']) ){
			$model->attributes = $_POST['QueryForm'];
			if($model->validate()){
				$searcher = new Searcher();
				$results = $searcher->Query( $model->query, $model->method );
			}
		}
		$this->render('index',
			[
				'model'	=> $model,
				'results' => $results
			]
		);
	}

	public function actionError(){
		if($error=Yii::app()->errorHandler->error)
		{
			if(Yii::app()->request->isAjaxRequest)
				echo $error['message'];
			else
				$this->render('error', $error);
		}
	}

	public function actionNeyronnetwork(){
		$dump = [
			'WIds'	=> [ 6766, 5139 ],
			'UIds'	=> [ 906, 1413, 591, 1308 ]
		];
		$nn = new NNetwork();

		$res = $nn->trainQuery($dump['WIds'], $dump['UIds'], 1413);
		$res = $nn->getResult($dump['WIds'], $dump['UIds']);
		var_dump( $res );
	}

}