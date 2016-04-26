<?php
/**
 * api 接口
 * 获取最新版本信息
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_api_appversion_get_last extends voa_c_api_appversion_base {

	public function execute() {

		// 需要的参数
		$fields = array(
			// 客户端类型
			'type' => array('type' => 'string', 'required' => true),
		);

		// 基本验证检查
		$this->_check_params($fields);

		$this->_params['type'] = rstrtolower($this->_params['type']);
		if (!isset($this->_app_client_type_map[$this->_params['type']])) {
			$this->_set_errcode(voa_errcode_api_appversion::VER_TYPE_UNKNOW);
			return false;
		}

		// 输出数据
		$this->_result = array();
		$this->_uda_appversion_get->last_by_clienttype($this->_params['type'], $this->_result);

		return true;
	}

}
