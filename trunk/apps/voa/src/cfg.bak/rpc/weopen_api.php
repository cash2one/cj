<?php
/**
 * rpc for weopen, 接收来自微信开放平台的接口配置
 *
 * $Author$
 * $Id$
 */

require_once dirname(__FILE__).'/open_common.php';

$allow_methods = array(
	'woevent.ticket', // ticket 事件消息
	'woevent.unauthorized' // unauthorized 事件消息
);
$conf = fetch_open_config($allow_methods);

// 从新定义某些专用配置，如 验证方式， 缓存时间 等
$conf['auth'] = true;
