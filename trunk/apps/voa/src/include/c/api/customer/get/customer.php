<?php
/**
 * voa_c_api_customer_get_customer
 * 获取客户列表信息
 * $Author$
 * $Id$
 */

class voa_c_api_customer_get_customer extends voa_c_api_customer_abstract {

	public function execute() {

		// 获取分页参数
		$page = (int)$this->_get('page');
		list($start, $perpage, $page) = voa_h_func::get_limit($page, $this->_p_sets['perpage']);

		// 读取数据
		$total = 0;
		$list = array();
		$uda = &uda::factory('voa_uda_frontend_customer_data', $this->_ptname);
		if (!$uda->list_all($this->_params, array($start, $perpage), $list, $total)) {
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
	protected function _set_ptname() {

		parent::_set_ptname();
		$this->_ptname['classes'] = voa_h_cache::get_instance()->get('plugin.customer.class', 'oa');
		$this->_ptname['columns'] = voa_h_cache::get_instance()->get('plugin.customer.tablecol', 'oa');
		$this->_ptname['options'] = voa_h_cache::get_instance()->get('plugin.customer.tablecolopt', 'oa');
	}

}
