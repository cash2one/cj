<?php
/**
 * voa_c_api_goods_delete_goods
 * 删除商品信息
 * $Author$
 * $Id$
 */

class voa_c_api_goods_delete_goods extends voa_c_api_goods_abstract {

	public function execute() {

		// 删除商品信息
		$uda = &uda::factory('voa_uda_frontend_goods_data', $this->_ptname);
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
		$this->_ptname['classes'] = voa_h_cache::get_instance()->get('plugin.goods.class', 'oa');
		$this->_ptname['columns'] = voa_h_cache::get_instance()->get('plugin.goods.tablecol', 'oa');
		$this->_ptname['options'] = voa_h_cache::get_instance()->get('plugin.goods.tablecolopt', 'oa');
	}

}
