<?php
	$this->pageTitle=Yii::app()->name;
?>

<h1>Welcome to <?php echo Yii::app()->name; ?></h1>

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'query-form',
	'enableClientValidation'=>true,
	'clientOptions'=>array(
		'validateOnSubmit'=>true,
	),
)); ?>

	<?php echo $form->textField($model,'query'); ?>

	<?php echo CHtml::submitButton('Search'); ?>

<?php $this->endWidget(); ?>
