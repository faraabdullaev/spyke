<?php
$this->pageTitle=Yii::app()->name;
?>

	<h1>Welcome to <?php echo Yii::app()->name; ?>(NN version)</h1>

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'nnquery-form',
	'enableClientValidation'=>true,
	'clientOptions'=>array(
		'validateOnSubmit'=>true,
	),
)); ?>

<?php echo $form->textField($model,'query'); ?>
<?php echo CHtml::submitButton('Search'); ?>
<?php echo $form->error($model,'query'); ?>
<?php $this->endWidget(); ?>

<?php if($results != null): ?>
	<table>
		<thead>
			<tr>
				<th>Score</th>
				<th>Url</th>
			</tr>
		</thead>
		<tbody>
		<?php foreach($results as $url => $score):?>
			<tr>
				<td>
					<?php echo number_format($score, 5); ?>
				</td>
				<td>
					<?php
						echo CHtml::link(
							$this->getUrl($url),
							'/set/'.$url,
							[
								'target' => '_blank'
							]
						);
					?>
				</td>
			</tr>
		<?php endforeach;?>
		</tbody>
	</table>
<?php endif; ?>