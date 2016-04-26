<?php
/**
 * voa_server_forward
 * 接口服务基类
 *
 * $Author$
 * $Id$
 */

class voa_server_forward extends voa_server_base {

	/**
	 * __construct
	 * 接口服务构造方法
	 *
	 * @param  mixed $serverName
	 * @return void
	 */
	public function __construct($server_name) {

		parent::__construct($server_name);

		/** 记录请求日志 */
		$this->_log();
	}
}
