<?php
/**
 * rpc for web, 企业 OA 的接口配置
 *
 * $Author$
 * $Id$
 */

require_once dirname(__FILE__).'/open_common.php';

// 允许外部访问的方法，使用：类映射名.方法名
$allow_methods = array(
	'experience.open', 'test.get', 'application.app_open_confirm', 'application.app_close_confirm', 'application.app_delete_confirm',
	'recognition.namecard', 'site.open', 'enterprise.update_corp', 'member.pwdmodify'
);
$conf = fetch_open_config($allow_methods);

/** 从新定义某些专用配置，如 验证方式， 缓存时间 等 */
$conf['auth'] = true;
