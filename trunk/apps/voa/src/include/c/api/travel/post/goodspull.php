<?php
/**
 * voa_c_api_travel_post_goodspull
 * 更新商品信息
 * $Author$
 * $Id$
 */

class voa_c_api_travel_post_goodspull extends voa_c_api_travel_goodsabstract {

	public function execute() {

		// 获取表格 dataid
		$dataid = 0;
		if (isset($this->_params['dataid'])) {
			$dataid = (int)$this->_params['dataid'];
		}

		// 读取货源数据
		$uda_src = &uda::factory('voa_uda_frontend_goods_data', $this->_ptname);
		$goods = array();
		if (!$uda_src->get_one($dataid, $goods)) {
			$this->_errcode = $uda_src->errno;
			$this->_errmsg = $uda_src->error;
			return true;
		}

		// 货源数据导入自己的列表
		$uda = &uda::factory('voa_uda_frontend_goods_data', $this->_ptname);
		if (!$uda->copy($this->_member, $goods['dataid'])) {
			$this->_errcode = $uda->errno;
			$this->_errmsg = $uda->error;
			return true;
		}

		$this->_result = $goods;

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
