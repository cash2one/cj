<?php
/**
 * voa_c_api_travel_get_setting
 * 获取配置列表
 * $Author$
 * $Id$
 */

class voa_c_api_travel_get_setting extends voa_c_api_travel_abstract {

	public function execute() {

		// 返回列表
		$this->_result = $this->_p_sets;

		return true;
	}

}
