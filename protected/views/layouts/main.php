<!DOCTYPE html>
<html>
<head>
	<link rel="shortcut icon" href="<?php echo Yii::app()->getBaseUrl();?>/favicon.png" type="image/png">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="language" content="en" />
	<style>
		.errorMessage{
			color: rgb(190, 0, 0);
			background: rgb(240, 190, 190);
		}
		#QueryForm_query{
			width: 360px;
		}
	</style>
	<title><?php echo CHtml::encode($this->pageTitle); ?></title>
</head>
<body>
	<?php echo $content; ?>
</body>
</html>
