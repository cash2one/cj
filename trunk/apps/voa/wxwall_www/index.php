<?php
/**
 * /wxwall_www/index.php
 * 微信墙前端入口
 * Create By Deepseath
 * $Author$
 * $Id$
 */
define('ROOT_PATH', dirname(dirname(dirname(dirname(__FILE__)))));
define('APP_PATH', dirname(dirname(__FILE__)));
/** 当前应用目录名（非物理目录名） */
define('APP_DIRNAME', 'wall');//trim(str_replace(APP_PATH, '', dirname(__FILE__)), DIRECTORY_SEPARATOR));
/** 静态文件目录Url */
define('APP_STATIC_URL', '/'.APP_DIRNAME.'/static/');

require_once(ROOT_PATH.'/framework/startup.php');

try {
	$options = array(
			'interface' => 'web',
			'profiler' => true, /** 一般用户调试 */
			'route' => 'voa.route.wxwall',
			'session' => 'session.www'
	);

	/** 如果是生产环境或者Ajax请求，则不调用profiler */
	if ( ( $_SERVER['RUN_MODE'] == 'production' && isset($_SERVER['RUN_MODE']) ) || ( isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' ) ) {
		unset($options['profiler']);
	}

	$startup = &startup::factory($options);
	$startup->run();
} catch(Exception $e) {
	/** 开发时才显示错误信息 */
	if ( isset($_SERVER['RUN_MODE']) && $_SERVER['RUN_MODE'] == 'development' ) {
		echo $e->getMessage();
	}
}
