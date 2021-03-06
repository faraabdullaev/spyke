<?php

/**
 * This is the model class for table "{{link}}".
 *
 * The followings are the available columns in table '{{link}}':
 * @property integer $id
 * @property integer $fromId
 * @property integer $toId
 */
class Link extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{link}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('fromId, toId', 'required'),
			array('fromId, toId', 'numerical', 'integerOnly'=>true),
			array('fromId;toId', 'gunique'),
			array('id, fromId, toId', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'fromId' => 'From',
			'toId' => 'To',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search()
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('fromId',$this->fromId);
		$criteria->compare('toId',$this->toId);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Link the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * Проверяет атрибуты модели на уникальность
	 */
	public function gunique($attribute)
	{
		$propnames = preg_split('/;/', $attribute);
		$search    = array();
		foreach ($propnames as $name) {
			if (isset($this->$name)) {
				$search[$name] = $this->$name;
			}
		}

		$class = get_class($this);
		$count = $class::model()->countByAttributes($search);

		if ($count > ($this->isNewRecord ? 0 : 1)) {
			$this->addError($attribute, implode($propnames, ', ') . " не уникальны");
		}

	}
}
