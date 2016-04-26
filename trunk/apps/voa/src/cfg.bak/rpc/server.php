<?php
/**
 * 服务接口配置
 *
 * $Author$
 * $Id$
 */

/** 密钥 */
$conf['auth_key'] = 'ouy%]^qx8,#4qxLQQjdvvizoF3wt^V=o';

/** 超时日志 */
$conf['timeout_logs'] = array(
	'enabled' => true,
	'ranges' => array(
		array(0, 1000) /** 单位: 毫秒 */
	)
);
