<?php
/**
 * voa_c_api_customer_post_customer
 * 更新客户信息
 * $Author$
 * $Id$
 */

class voa_c_api_customer_post_customer extends voa_c_api_customer_abstract {

	public function execute() {

		// 获取表格 dataid
		$dataid = 0;
		if (isset($this->_params['dataid'])) {
			$dataid = (int)$this->_params['dataid'];
		}

		// 更新数据
		$uda = &uda::factory('voa_uda_frontend_customer_data', $this->_ptname);
		if (0 < $dataid) {
			if (!$uda->update($this->_member, $this->_params, $dataid)) {
				$this->_errcode = $uda->errno;
				$this->_errmsg = $uda->error;
				return true;
			}
		} else {
			$customer = array();
			if (!$uda->add($this->_member, $this->_params, $customer)) {
				$this->_errcode = $uda->errno;
				$this->_errmsg = $uda->error;
				return true;
			}

			$this->_result = $customer;
		}

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
