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
	<?php echo $form->error($model,'query'); ?>
	<br>
	<span>Method :</span>
	<?php echo $form->radioButtonList($model,'method',
			[
				1 => 'Location',
				2 => 'Distance',
				3 => 'Frequency',
				4 => 'Page Rank',
			],
			['separator'=>' ']
		);
	?>
	<?php echo $form->error($model,'method'); ?>
	<br>
	<br>

<?php $this->endWidget(); ?>

<?php
	if($results != null){
		$i = 0;
		foreach( $results as $position ){
			$i++;
			echo number_format($position['score'], 5);
			echo '&nbsp;&nbsp;&nbsp;&nbsp;';
			echo $position['url'];
			//echo CHtml::link($position['url'], $position['url']);
			echo '<br>';
		}
		echo 'Total: '.$i;
	} else
		echo 'No result';
?>