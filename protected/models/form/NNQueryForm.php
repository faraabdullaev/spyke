<?php

class NNQueryForm extends CFormModel{
	public $query;

	public function rules(){
		return [
			['query', 'required'],
			['query', 'length', 'max'=>255, 'min'=>3],
		];
	}

	public function attributeLabels(){
		return [
			'query'=>Yii::t('main', 'Query row'),
		];
	}

}
