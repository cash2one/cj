<?php
/**
 * voa_c_api_travel_delete_goods
 * 删除商品信息
 * $Author$
 * $Id$
 */

class voa_c_api_travel_delete_goods extends voa_c_api_travel_goodsabstract {

	public function execute() {

		// 获取产品ID
		$dataid = (int)$this->_get('dataid');
		if (empty($dataid)) {
			$this->_set_errcode(voa_errcode_oa_goods::GOODS_DATAID_IS_EMPTY);
			return true;
		}

		// 删除商品信息
		$uda = &uda::factory('voa_uda_frontend_goods_data', $this->_ptname);
		if (!$uda->delete($dataid)) {
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
	protected function _init_ptname() {

		parent::_init_ptname();
		$this->_ptname['classes'] = voa_h_cache::get_instance()->get('plugin.travel.goodsclass', 'oa');
		$this->_ptname['columns'] = voa_h_cache::get_instance()->get('plugin.travel.goodstablecol', 'oa');
		$this->_ptname['options'] = voa_h_cache::get_instance()->get('plugin.travel.goodstablecolopt', 'oa');
	}

}
