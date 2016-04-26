<?php
/**
 * 开放接口, 普通路由
 * $Author$
 * $Id$
 */

define('ROOT_PATH', dirname(dirname(dirname(dirname(__FILE__)))));
define('APP_PATH', dirname(dirname(__FILE__)));

require_once(ROOT_PATH.'/framework/startup.php');

try {
	$options = array(
		'interface' => 'web',
		'profiler' => true,
		'route' => 'voa.route.open',
	);

	/** 如果是生产环境或者Ajax请求，则不调用 profiler */
	if ($_SERVER['RUN_MODE'] == 'production' || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
		unset($options['profiler']);
	}

	$startup =& startup::factory($options);
	$startup->run();
} catch(Exception $e) {
	/** 开发时才显示错误信息 */
	if ($_SERVER['RUN_MODE'] == 'development') {
		echo $e->getMessage();
	}
}
