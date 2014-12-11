<?php

$private = require_once dirname(__FILE__) . '/private.php';

$config =  array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'Spyke',

	'preload'=>array('debug'),

	'import'=>array(
		'application.models.db.*',
		'application.components.SpiderEngine.*',
	),

	'components'=>array(
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'error, warning',
				),
			),
		),
	),
);
return array_merge($config, $private);
