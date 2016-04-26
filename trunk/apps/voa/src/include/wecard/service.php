<?php
/**
 * 门禁操作
 * $Author$
 * $Id$
 */

class voa_wecard_service extends voa_wecard_base {


	// 构造函数, 初始化
	public function __construct() {

		parent::__construct();
	}

	// 开门
	public function open_door($sn, $index, $ip, $port) {

		$this->_open($sn, $index, $ip, $port);
		return true;
	}
}
