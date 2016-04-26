<?php
/**
 * rpc for wxqy suite, 接收来自微信企业号套件服务器的接口配置
 *
 * $Author$
 * $Id$
 */

require_once dirname(__FILE__).'/open_common.php';

$allow_methods = array(
	'wxqysuite.suite_ticket', /** 推送 suite_ticket */
	'wxqysuite.change_auth', /** 变更授权 */
	'wxqysuite.cancel_auth' /** 取消授权 */
);
$conf = fetch_open_config($allow_methods);

/** 从新定义某些专用配置，如 验证方式， 缓存时间 等 */
$conf['auth'] = true;
