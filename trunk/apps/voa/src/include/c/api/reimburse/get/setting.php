<?php
/**
 * voa_c_api_reimburse_get_setting
 * 获取任务应用的系统配置接口
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_api_reimburse_get_setting extends voa_c_api_reimburse_base {

	public function execute() {

		// 报销类型
		$types =  $this->_p_sets['types'];

		$this->_result = array(
			'types' => $types
		);

		return true;
	}

}
