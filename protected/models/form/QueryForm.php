<?php

class QueryForm extends CFormModel{
	const METHOD_LOCATION		= 1;
	const METHOD_DISTANCE		= 2;
	const METHOD_FREQUENCY		= 3;
	const METHOD_PAGERANK		= 4;
	const METHOD_NEURAL_NETWORK	= 5;

	public $query;
	public $method;

	public function rules(){
		return [
			['query, method', 'required'],
			['query', 'length', 'max'=>255, 'min'=>3],
			['method', 'numerical', 'integerOnly'=>true],
		];
	}

	public function attributeLabels(){
		return [
			'query'=>Yii::t('main', 'Query row'),
			'method'=>Yii::t('main', 'Range method'),
		];
	}

}
