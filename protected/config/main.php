<?php

return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'Spyke',

	'preload'=>array('debug'),

	'import'=>array(
		'application.models.db.*',
		'application.models.db.nnhd.*',
		'application.models.form.*',
		'application.components.*',
		'application.components.Archer.*',
		'application.components.SpiderEngine.*',
	),

	'modules'=>array(
		'gii'=>array(
			'class'=>'system.gii.GiiModule',
			'password'=>'admin',
			'ipFilters'=>array('127.0.0.1','::1'),
		),
	),

	'components'=>array(
		'user'=>array(
			'allowAutoLogin'=>true,
		),

		'urlManager'=>array(
			'urlFormat'=>'path',
			'rules'=>array(
				''					=> 'site/index',
				'neyronnetwork'		=> 'neyron/index',
				'set/<id:\d+>'		=> 'neyron/set',

				'<controller:\w+>/<id:\d+>'=>'<controller>/view',
				'<controller:\w+>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',
				'<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
			),
			'showScriptName' => false,
			'caseSensitive' => false,
		),
		'errorHandler'=>array(
			'errorAction'=>'site/error',
		),
	),

	'params'=>array(
		'adminEmail'=>'faraabdullaev@gmail.com',
		'author' => 'fara',
	),
);