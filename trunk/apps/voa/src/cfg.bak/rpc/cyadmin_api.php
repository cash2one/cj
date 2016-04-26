<?php
/**
 * rpc for cyadmin, 主站后台的接口配置
 *
 * $Author$
 * $Id$
 */

require_once dirname(__FILE__).'/open_common.php';

// 允许外部访问的方法，使用：类映射名.方法名
$allow_methods = array(
	'cyadmin_enterprise.update_profile'
);
$conf = fetch_open_config($allow_methods);

/** 从新定义某些专用配置，如 验证方式， 缓存时间 等 */
$conf['auth'] = true;

$conf['api_url'] = 'http://cy.admin.vchangyi.com/api.php';