<?php

class QueryForm extends CFormModel{
	public $query;
	public $method;

	public function rules(){
		return [
			['query', 'required'],
			['query', 'length', 'max'=>255, 'min'=>3],
			['method', 'numerical', 'integerOnly'=>true],
		];
	}

	public function attributeLabels(){
		return [
			'query'=>Yii::t('main', 'Ur query'),
			'method'=>Yii::t('main', 'Range method'),
		];
	}

}
