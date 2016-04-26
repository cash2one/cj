<?php
/**
 * rpc for web, ucenter 的接口配置
 *
 * $Author$
 * $Id$
 */

require_once dirname(__FILE__).'/open_common.php';

$allow_methods = array(
	'member.get',
	'member.edit'
);
$conf = fetch_open_config($allow_methods);

/** 从新定义某些专用配置，如 验证方式， 缓存时间 等 */
$conf['auth'] = true;
