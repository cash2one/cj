<?php
/**
 * api 接口
 * app 版本列表
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_api_appversion_get_list extends voa_c_api_appversion_base {

	public function execute() {

		// 需要的参数
		$fields = array(
			// 客户端类型
			'type' => array('type' => 'string', 'required' => true),
			// 当前页码
			'page' => array('type' => 'int', 'required' => true),
			// 每页显示条数
			'limit' => array('type' => 'int', 'required' => true),
			// 排序方式
			'order' => array('type' => 'string', 'required' => false)
		);

		// 基本验证检查
		$this->_check_params($fields);

		$this->_params['type'] = rstrtolower($this->_params['type']);
		if (!isset($this->_app_client_type_map[$this->_params['type']])) {
			$this->_set_errcode(voa_errcode_api_appversion::VER_TYPE_UNKNOW);
			return false;
		}

		$order = 'DESC';
		if (in_array(rstrtolower($this->_params['order']), array('asc', 'desc'))) {
			$order = rstrtoupper($this->_params['order']);
		}

		// 输出数据
		$this->_result = array();
		$this->_uda_appversion_get->list_by_clienttype($this->_params['type'], $this->_params['page'], $this->_params['limit'], $order, $this->_result);
		return true;
	}

}
