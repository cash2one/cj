<?php
/**
 * 后台程序入口
 *
 * 执行某个程序的方法：
 *   $ php command.php -n controller别名 [args]
 * 例如：
 *   $ php command.php -n toolTest
 *   $ php command.php -n toolTest -f 10 -t 20
 *   其中，参数使用$this->_opt['key']获取
 *
 * $Author$
 * $Id$
 */

define('ROOT_PATH', dirname(dirname(dirname(dirname(__FILE__)))));
define('APP_PATH', dirname(dirname(__FILE__)));

error_reporting(E_ALL ^ E_NOTICE);
require_once(ROOT_PATH.'/framework/startup.php');

try {
	$options = array(
		'interface' => 'cli',
		'controllers' => array(
			'tooltest' => 'voa_backend_tool_test',
			'tasktest' => 'voa_backend_task_test',
			'crontest' => 'voa_backend_cron_test',
			'synccard' => 'voa_backend_cron_synccard'
		)
	);
	$startup = startup::factory($options);
	$startup->run();
} catch(Exception $e) {
	echo 'ERROR: '.$e->getMessage();
	echo "\n";
	exit;
}
