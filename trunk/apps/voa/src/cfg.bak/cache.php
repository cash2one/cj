<?php
/**
 * 缓存配置文件
 *
 * $Author$
 * $Id$
 */

/** 用户站点信息缓存目录 */
$conf['sitedir'] = '/tmp/site';

/** mysql 表缓存路径 */
$conf['mtc.path'] = '/tmp/cache';

/** 所有缓存组名 */
$conf['mtc.groups'] = array('oa', 'uc', 'cyadmin', 'ucenter');

/** mysql 表系统缓存配置 */
$service_1 = &service::factory('voa_s_oa_mtc', array('pluginid' => startup_env::get('pluginid')));
$conf['mtc.oa'] = array(
	'class' => 'cache_memory', /** 处理类 */
	'service' => $service_1,
	'keys' => array('setting', 'cpmenu'), /** 所有缓存名 */
	'options' => array() /** 类参数 */
);

$conf['mtc.ucenter'] = array(
	'class' => 'cache_redis',
	'service' => $service_1,
	'keys' => array(),
	'options' => array(
		'host' => '127.0.0.1',
		'port' => 6379,
		'pwd' => ''
	)
);

/** ucenter 缓存 */
$service_2 = &service::factory('voa_s_uc_mtc', array('pluginid' => startup_env::get('pluginid')));
$conf['mtc.uc'] = array(
	'class' => 'cache_memory',
	'service' => $service_2,
	'keys' => array(),
	'options' => array()
);

/** 畅移主站缓存 */
/*$service_3 = &service::factory('voa_s_main_mtc', array('pluginid' => startup_env::get('pluginid')));
$conf['mtc.main'] = array(
	'class' => 'cache_memory',
	'service' => $service_3,
	'keys' => array(),
	'options' => array()
);*/

/** 畅移主站后台缓存 */
$service_cyadmin = &service::factory('voa_s_cyadmin_mtc', array('pluginid' => startup_env::get('pluginid')));
$conf['mtc.cyadmin'] = array(
	'class' => 'cache_memory',
	'service' => $service_cyadmin,
	'keys' => array(),
	'options' => array()
);
