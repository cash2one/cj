<?php

/**
 * 获取签到配置信息
 * $Author$
 * $Id$
 */
class voa_c_api_sign_get_config extends voa_c_api_sign_base {

	public function execute() {

		/** 设置返回值 */
		$this->_result = $this->_p_sets;

		return true;
	}
}
