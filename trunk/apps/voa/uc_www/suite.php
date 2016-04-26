<?php
/**
 * 企业号套件接口
 *
 * $Author$
 * $Id$
 */

define('ROOT_PATH', dirname(dirname(dirname(dirname(__FILE__)))));
define('APP_PATH', dirname(dirname(__FILE__)));
define('SERVER_LOG', 1);

error_reporting(E_ERROR);

require_once(ROOT_PATH.'/framework/startup.php');

try {
	$options = array('interface' => 'runtime');
	$startup =& startup::factory($options);
	$startup->run();

	startup_env::set_benchmark('request_started');

	/** api 服务 */
	$server = new voa_server_suite_forward('suite_api');
	$server->handle();

} catch(Exception $e) {

	/** 调试信息 */
	echo $e->getMessage();
}
