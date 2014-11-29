<?php

class QueryForm extends CFormModel{
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
