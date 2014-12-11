<?php

class SiteController extends Controller {

	public function actionIndex(){
		$model = new QueryForm;
		$results = null;
		if( isset($_POST['QueryForm']) ){
			$model->attributes = $_POST['QueryForm'];
			if($model->validate()){
				$searcher = new Searcher;
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

}