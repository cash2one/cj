<?php
/**
 * voa_c_api_travel_post_goodssellerup
 * 编辑商品信息
 * $Author$
 * $Id$
 */

class voa_c_api_travel_post_goodssellerup extends voa_c_api_travel_goodsabstract {

	public function execute() {

		// 获取产品 dataid
		$dataid = (int)$this->_get('dataid');
		$t = new voa_d_oa_goods_data();
		// 读取数据
		$goods = array();
		if (!$goods = $t->get($dataid)) {
			$this->_set_errcode(voa_errcode_oa_goods::GOODS_DATA_IS_NOT_EXIST);
			return true;
		}

		// 判断是否有权限
		if ($this->_member['m_uid'] != $goods['uid']) {
			$this->_set_errcode(voa_errcode_oa_goods::NO_EDIT_PRIVILEGE);
			return true;
		}

		// 编辑数据
		$uda = &uda::factory('voa_uda_frontend_travel_goods', $this->_ptname);
		if (!$uda->seller_edit($this->_member, $this->_params, $dataid, $goods)) {
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
