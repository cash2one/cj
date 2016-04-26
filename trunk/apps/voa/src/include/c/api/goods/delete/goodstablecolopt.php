<?php
/**
 * voa_c_api_goods_delete_goodstablecolopt
 * 删除表格列选项信息
 * $Author$
 * $Id$
 */

class voa_c_api_goods_delete_goodstablecolopt extends voa_c_api_goods_abstract {

	public function execute() {

		// 删除表格列选项信息
		$uda = &uda::factory('voa_uda_frontend_goods_tablecolopt', $this->_ptname);
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
		$this->_ptname['tablecolopts'] = voa_h_cache::get_instance()->get('plugin.goods.tablecolopt', 'oa');
	}

}
