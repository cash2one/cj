<?php
/**
 * cache 默认配置
 * $Author$
 * $Id$
 */

/** 页面缓存的大小 */
$conf['page_cache_num'] = 600;

/** setting 缓存配置(memcache) */
$cluster1 = array(
	array('host' => '192.168.0.233', 'port' => 11211),
	array('host' => '192.168.0.234', 'port' => 11211)
);
$cluster2 = array(
	array('host' => '192.168.0.233', 'port' => 11211),
	array('host' => '192.168.0.234', 'port' => 11211)
);
$conf['setting_mc'] = array(
	'ttl' => 0, /** memcache 用 */
	'class' => 'cache_memcached', /** 处理类 */
	'options' => array( /** 类参数 */
		'servers' => array($cluster2, $cluster3),
		'auto_replication' => true,
		'flag'	=> 0
	)
);