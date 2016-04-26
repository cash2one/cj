<?php
/**
 * 请求接口配置
 *
 * $Author$
 * $Id$
 */

/** 密钥 */
$conf['auth_key'] = 'ouy%]^qx8,#4qxLQQjdvvizoF3wt^V=o';

/** 测试接口 */
$conf['test'] = array(
	'method' => 'test.get',
	'url' => 'http://demo.vchangyi.com/api.php'
);

/** 请求 voa experience 接口配置 */
$conf['experience'] = array(
   'method' => 'experience.open',
   'url' => 'http://demo.vchangyi.com/api.php'
);

/** 请求 ucenter 接口配置 */
$conf['uc_company_register'] = array(
	'method' => 'company.register',
	'url' => 'http://uc.vchangyi.com/api.php'
);

/** 请求 voa 接口配置 */
$conf['voa_auto_open'] = array(
	'method' => 'oa.autoopen',
	'url' => 'http://oa.vchangyi.com/api.php'
);
