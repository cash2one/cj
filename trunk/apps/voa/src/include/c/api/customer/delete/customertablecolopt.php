<?php
/**
 * voa_c_api_customer_delete_customertablecolopt
 * 删除表格列选项信息
 * $Author$
 * $Id$
 */

class voa_c_api_customer_delete_customertablecolopt extends voa_c_api_customer_abstract {

	public function execute() {

		// 删除表格列选项信息
		$uda = &uda::factory('voa_uda_frontend_customer_tablecolopt', $this->_ptname);
		if (!$uda->delete($this->_params)) {
			$this->_errcode = $uda->errno;
			$this->_errmsg = $uda->error;
			return true;
		}

		return true;
	}

	/**
	 * 设置插件/表格名称
	 * @return boolean
	 */
	protected function _set_ptname() {

		parent::_set_ptname();
		$this->_ptname['tablecolopts'] = voa_h_cache::get_instance()->get('plugin.customer.tablecolopt', 'oa');
	}

}
