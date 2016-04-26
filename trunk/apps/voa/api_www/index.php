<?php
/**
 * index.php
 * API 入口
 * Create By Deepseath
 * $Author$
 * $Id$
 */
define('ROOT_PATH', dirname(dirname(dirname(dirname(__FILE__)))));
define('APP_PATH', dirname(dirname(__FILE__)));

// 定义管理目录，如果不使用目录方式则设置此值为空，该值影响后台route规则缓存的生成
// define('APP_DIRNAME', trim(str_replace(APP_PATH, '', dirname(__FILE__)), DIRECTORY_SEPARATOR));
define('APP_DIRNAME', '');

require_once(ROOT_PATH.'/framework/startup.php');

try {
	$options = array(
			'interface' => 'web',
			'profiler' => true, /** 一般用户调试 */
			'route' => 'voa.route.api',
			'handle' => true,
			'session' => 'voa.session.www'
	);

	/** 如果是生产环境或者Ajax请求，则不调用profiler */
	if ((isset($_SERVER['RUN_MODE']) && $_SERVER['RUN_MODE'] == 'production') || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')) {
		unset($options['profiler']);
	}

	$startup = &startup::factory($options);
	$startup->run();
} catch(Exception $e) {
	/** 开发时才显示错误信息 */
	if (isset($_SERVER['RUN_MODE']) && $_SERVER['RUN_MODE'] == 'development') {
		echo $e->getMessage();
	}
}
