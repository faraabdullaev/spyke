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
	<br>
	<span>Method :</span>
	<?php echo $form->radioButtonList($model,'method',
			[
				QueryForm::METHOD_LOCATION			=> 'Location',
				QueryForm::METHOD_DISTANCE			=> 'Distance',
				QueryForm::METHOD_FREQUENCY			=> 'Frequency',
				QueryForm::METHOD_PAGERANK			=> 'Page Rank',
				QueryForm::METHOD_NEURAL_NETWORK	=> 'Neural Network',
			],
			['separator'=>' ']
		);
	?>
	<?php echo $form->error($model,'query'); ?>
	<?php echo $form->error($model,'method'); ?>
	<br>
	<br>

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