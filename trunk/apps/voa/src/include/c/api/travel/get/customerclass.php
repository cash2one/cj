<?php
/**
 * voa_c_api_travel_get_customerclass
 * 获取分类信息
 * $Author$
 * $Id$
 */

class voa_c_api_travel_get_customerclass extends voa_c_api_travel_customerabstract {

	public function execute() {

		// 读取数据
		$total = 0;
		$list = array();
		$uda = &uda::factory('voa_uda_frontend_customer_class', $this->_ptname);
		if (!$uda->list_all($this->_params, $list, $total)) {
			$this->_errcode = $uda->errno;
			$this->_errmsg = $uda->error;
			return true;
		}

		$this->_result = array(
			'total' => $total,
			'data' => empty($list) ? array() : array_values($list)
		);

		return true;
	}

	/**
	 * 设置插件/表格名称
	 * @return boolean
	 */
	protected function _init_ptname() {

		parent::_init_ptname();
		$this->_ptname['classes'] = voa_h_cache::get_instance()->get('plugin.travel.customerclass', 'oa');
	}

}

