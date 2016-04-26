<?php
/**
 * 后台程序入口
 *
 * 后台入口程序，由 cron/tool/task 引入调用
 *
 * $Author$
 * $Id$
 */

if (!defined('CLI_CMD')) {
	echo 'Can not call itself.'.PHP_EOL;
	exit;
}

/** 设置运行环境 */
$_SERVER['RUN_MODE'] = 'development';
//$_SERVER['RUN_MODE'] = 'production';

define('ROOT_PATH', dirname(dirname(dirname(dirname(__FILE__)))));
define('APP_PATH', dirname(dirname(__FILE__)));

error_reporting(E_ALL ^ E_NOTICE);
require_once(ROOT_PATH.'/framework/startup.php');

try {
	$app_name = basename(APP_PATH);
	$ctl_name = '';
	for ($ai = 0; $ai < count($argv); ++ $ai) {
		if ($argv[$ai] == '-n' || $argv['ai'] == '--n') {
			$ctl_name = $argv[$ai + 1];
		}
	}

	$options = array(
		'interface' => 'cli',
		'controllers' => array(
			$ctl_name => $app_name.'_backend_'.CLI_CMD.'_'.$ctl_name
		)
	);

	$startup = startup::factory($options);
	$startup->run();
} catch(Exception $e) {
	echo 'ERROR: '.$e->getMessage();
	echo "\n";
	exit;
}
