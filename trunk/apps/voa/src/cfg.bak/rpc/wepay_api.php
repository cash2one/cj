<?php
/**
 * rpc for wepay, 接收来自微信支付服务器的接口配置
 *
 * $Author$
 * $Id$
 */

require_once dirname(__FILE__).'/open_common.php';

$allow_methods = array(
	'wepay_notify.order' /** 微信支付通知消息 */
);
$conf = fetch_open_config($allow_methods);

/** 从新定义某些专用配置，如 验证方式， 缓存时间 等 */
$conf['auth'] = true;
