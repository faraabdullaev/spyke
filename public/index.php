<?php

defined('YII_DEBUG') or define('YII_DEBUG',true);
defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL',3);

$yii    = dirname(__FILE__) . '/../vendor/yiisoft/yii/framework/yii.php';
$vendor = dirname(__FILE__) . '/../vendor/autoload.php';

require_once($yii);
require_once($vendor);

$config = require_once dirname(__FILE__) . '/../protected/config/main.php';
$private = require_once dirname(__FILE__) . '/../protected/config/private.php';

Yii::createWebApplication(CMap::mergeArray($config, $private))->run();
